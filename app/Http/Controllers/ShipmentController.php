<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Notification;
use App\Models\Project;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Pengiriman {$shipment->code} dibuat dan ditugaskan ke driver.");
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
