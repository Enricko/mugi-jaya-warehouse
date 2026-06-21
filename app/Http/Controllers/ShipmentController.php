<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Shipment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Shipment::with(['driver', 'project', 'warehouse']);
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return view('shipments.index', [
            'shipments' => $query->latest()->paginate(15)->withQueryString(),
            'statusFilter' => $status ?? null,
            'counts' => Shipment::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
        ]);
    }

    public function create(): View
    {
        return view('shipments.create', [
            'projects' => Project::whereIn('status', ['active', 'planning'])->orderBy('name')->get(),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'drivers' => User::where('role', 'driver')->orderBy('full_name')->get(),
            'materials' => Material::orderBy('name')->get(),
            'materialsJson' => Material::orderBy('name')->get()->map(fn ($m) => [
                'id' => $m->id, 'name' => $m->name, 'sku' => $m->sku, 'unit' => $m->unit,
            ])->values(),
            // General stock per warehouse+material, so the form can show what is
            // available and stop users tripping the "stok tidak mencukupi" block.
            'stockJson' => WarehouseStock::whereNull('project_id')->get()
                ->mapWithKeys(fn ($s) => ["{$s->warehouse_id}|{$s->material_id}" => (float) $s->quantity]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'driver_id' => 'required|exists:users,id',
            'vehicle_plate' => 'required|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        // Sum the requested quantity per material (defensive against a material
        // appearing in more than one item row).
        $demand = collect($data['items'])->groupBy('material_id')
            ->map(fn ($rows) => (float) collect($rows)->sum('quantity'));

        // General (project_id = null) stock available in the source warehouse —
        // the same pool inbound/transfer draw from.
        $available = WarehouseStock::where('warehouse_id', $data['warehouse_id'])
            ->whereIn('material_id', $demand->keys())
            ->whereNull('project_id')
            ->get()->keyBy('material_id');

        // Block the shipment if any item is short — same guard as transfers.
        $short = [];
        foreach ($demand as $materialId => $qty) {
            $have = (float) ($available[$materialId]->quantity ?? 0);
            if ($have < $qty) {
                $m = Material::find($materialId);
                $short[] = "{$m->name} (butuh " . $this->trimQty($qty) . ', tersedia ' . $this->trimQty($have) . " {$m->unit})";
            }
        }
        if ($short) {
            return back()->withInput()
                ->with('error', 'Stok gudang tidak mencukupi: ' . implode('; ', $short) . '.');
        }

        $lowStock = [];

        $shipment = DB::transaction(function () use ($data, $request, $demand, &$lowStock) {
            $shipment = Shipment::create([
                'code' => 'SHP-' . str_pad((string) (Shipment::count() + 899), 4, '0', STR_PAD_LEFT),
                'project_id' => $data['project_id'],
                'warehouse_id' => $data['warehouse_id'],
                'driver_id' => $data['driver_id'],
                'vehicle_plate' => strtoupper($data['vehicle_plate']),
                'status' => 'confirmed',
            ]);

            foreach ($data['items'] as $item) {
                $shipment->items()->create([
                    'material_id' => $item['material_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            // Deduct general stock and record one `consumption` transaction per
            // material so inventory, reports, the dashboard chart and the project
            // page all reflect the outflow.
            $base = Transaction::where('type', 'consumption')->count();
            $i = 0;
            foreach ($demand as $materialId => $qty) {
                $row = WarehouseStock::where('warehouse_id', $data['warehouse_id'])
                    ->where('material_id', $materialId)
                    ->whereNull('project_id')
                    ->lockForUpdate()->first();
                $row->quantity = (float) $row->quantity - $qty;
                $row->save();

                $material = Material::find($materialId);
                if ($row->quantity <= $material->min_stock) {
                    $lowStock[] = "{$material->name} (" . $this->trimQty((float) $row->quantity) . " {$material->unit})";
                }

                Transaction::create([
                    'code' => 'CON-' . str_pad((string) ($base + 306 + $i++), 4, '0', STR_PAD_LEFT),
                    'type' => 'consumption',
                    'status' => 'completed',
                    'reference_id' => $shipment->id,
                    'from_warehouse_id' => $data['warehouse_id'],
                    'project_id' => $data['project_id'],
                    'material_id' => $materialId,
                    'quantity' => $qty,
                    'created_by' => $request->user()->id,
                    'amount' => $qty * (float) $material->purchase_price,
                    'notes' => "Pengiriman {$shipment->code} ke proyek",
                ]);
            }

            return $shipment;
        });

        if ($lowStock) {
            foreach (User::whereIn('role', ['owner', 'kepala_gudang'])->pluck('id') as $id) {
                Notification::create([
                    'user_id' => $id,
                    'title' => 'Stok Kritis',
                    'message' => "Setelah {$shipment->code}, stok menipis: " . implode('; ', $lowStock) . '.',
                    'type' => 'alert',
                    'module' => 'Warehouse',
                    'is_read' => false,
                ]);
            }
        }

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Pengiriman {$shipment->code} dibuat — stok gudang dikurangi & konsumsi tercatat.");
    }

    /** Format a decimal quantity without trailing zeros (e.g. 8.00 → "8"). */
    private function trimQty(float $v): string
    {
        return rtrim(rtrim(number_format($v, 2), '0'), '.');
    }

    public function show(Shipment $shipment): View
    {
        $shipment->load(['driver', 'project', 'warehouse', 'items.material']);

        return view('shipments.show', compact('shipment'));
    }

    public function updateStatus(Request $request, Shipment $shipment): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:confirmed,in_transit,delivered,problem',
        ]);

        $shipment->status = $data['status'];
        if ($data['status'] === 'delivered') {
            $shipment->delivered_at = now();
        }
        $shipment->save();

        foreach (User::whereIn('role', ['owner', 'kepala_gudang'])->pluck('id') as $id) {
            Notification::create([
                'user_id' => $id,
                'title' => 'Status Pengiriman Berubah',
                'message' => "{$shipment->code} sekarang berstatus " . str_replace('_', ' ', $data['status']) . '.',
                'type' => $data['status'] === 'problem' ? 'alert' : 'info',
                'module' => 'Shipment',
                'is_read' => false,
            ]);
        }

        return back()->with('success', "Status {$shipment->code} diperbarui.");
    }
}
