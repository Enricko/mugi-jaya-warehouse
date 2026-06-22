<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Notification;
use App\Models\PurchaseOrder;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InboundController extends Controller
{
    public function index(): View
    {
        $inbounds = Transaction::with(['creator', 'material', 'toWarehouse'])
            ->where('type', 'inbound')->latest()->paginate(15);

        return view('inbound.index', compact('inbounds'));
    }

    public function create(): View
    {
        $pos = PurchaseOrder::with(['items.material', 'supplier'])->whereIn('status', ['approved', 'ordered'])->get();

        $poItems = $pos->mapWithKeys(fn ($po) => [
            $po->id => $po->items->map(fn ($i) => [
                'material_id' => $i->material_id,
                'sku' => $i->material->sku,
                'name' => $i->material->name,
                'qty' => (float) $i->quantity,
                'unit' => $i->material->unit,
            ])->values(),
        ]);

        $materials = Material::orderBy('name')->get();

        return view('inbound.create', [
            'pos' => $pos,
            'poItems' => $poItems,
            'poWarehouse' => $pos->pluck('warehouse_id', 'id'),
            'warehouses' => Warehouse::orderBy('name')->get(),
            'materials' => $materials,
            'materialsJson' => $materials->map(fn ($m) => [
                'id' => $m->id, 'name' => $m->name, 'sku' => $m->sku, 'unit' => $m->unit,
            ])->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'source' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.qty_actual' => 'required|numeric|min:0',
            'items.*.qty_po' => 'nullable|numeric|min:0',
            'items.*.location_tag' => 'nullable|string|max:50',
            'photos.*' => 'nullable|image|max:5120',
        ]);

        $amount = 0;
        $selisih = [];

        $transaction = DB::transaction(function () use ($data, $request, &$amount, &$selisih) {
            foreach ($data['items'] as $item) {
                $material = Material::find($item['material_id']);
                $qty = (float) $item['qty_actual'];
                $amount += $qty * (float) $material->purchase_price;

                if (isset($item['qty_po']) && (float) $item['qty_po'] != $qty) {
                    $diff = $qty - (float) $item['qty_po'];
                    $selisih[] = "{$material->name}: " . ($diff > 0 ? '+' : '') . rtrim(rtrim(number_format($diff, 2), '0'), '.') . " {$material->unit}";
                }

                $stock = WarehouseStock::firstOrNew([
                    'warehouse_id' => $data['warehouse_id'],
                    'material_id' => $material->id,
                    'project_id' => null,
                ]);
                $stock->quantity = (float) ($stock->quantity ?? 0) + $qty;
                if (! empty($item['location_tag'])) {
                    $stock->location_tag = $item['location_tag'];
                }
                $stock->save();
            }

            $note = $data['notes'] ?? '';
            if ($selisih) {
                $note = trim($note . ' | SELISIH: ' . implode('; ', $selisih));
            }

            $tx = Transaction::create([
                'code' => 'INB-' . str_pad((string) (Transaction::where('type', 'inbound')->count() + 211), 4, '0', STR_PAD_LEFT),
                'type' => 'inbound',
                'status' => 'completed',
                'reference_id' => $data['purchase_order_id'] ?? null,
                'to_warehouse_id' => $data['warehouse_id'],
                'material_id' => $data['items'][0]['material_id'],
                'quantity' => collect($data['items'])->sum('qty_actual'),
                'created_by' => $request->user()->id,
                'amount' => $amount,
                'notes' => $note ?: null,
            ]);

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $path = $photo->store('transaction-photos', 'public');
                    $tx->photos()->create([
                        'photo_path' => $path,
                        'captured_at' => now(),
                    ]);
                }
            }

            if (! empty($data['purchase_order_id'])) {
                PurchaseOrder::where('id', $data['purchase_order_id'])->update(['status' => 'received']);
            }

            return $tx;
        });

        foreach (User::where('role', 'kepala_gudang')->pluck('id') as $id) {
            Notification::create([
                'user_id' => $id,
                'title' => 'Barang Masuk Dicatat',
                'message' => "{$transaction->code} dicatat oleh " . $request->user()->full_name . ($selisih ? ' (ada selisih)' : ''),
                'type' => $selisih ? 'warning' : 'info',
                'module' => 'Warehouse',
                'is_read' => false,
            ]);
        }

        return redirect()->route('inbound.index')->with('success', "Barang masuk {$transaction->code} berhasil dicatat dan stok diperbarui.");
    }
}
