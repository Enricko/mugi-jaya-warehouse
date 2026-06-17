<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Project;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = WarehouseStock::with(['material', 'warehouse', 'project']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('material', fn ($m) => $m->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"))
                  ->orWhere('location_tag', 'like', "%{$search}%");
            });
        }
        if ($wh = $request->get('warehouse')) {
            $query->where('warehouse_id', $wh);
        }
        if ($cat = $request->get('category')) {
            $query->whereHas('material', fn ($m) => $m->where('category', $cat));
        }
        if ($proj = $request->get('project')) {
            $proj === 'umum' ? $query->whereNull('project_id') : $query->where('project_id', $proj);
        }

        $status = $request->get('status');
        if ($status === 'critical') {
            $query->whereExists(function ($q) {
                $q->selectRaw('1')->from('materials')
                    ->whereColumn('materials.id', 'warehouse_stock.material_id')
                    ->whereColumn('warehouse_stock.quantity', '<=', 'materials.min_stock');
            });
        } elseif ($status === 'untagged') {
            $query->whereNull('location_tag');
        }

        $stocks = $query->orderBy('warehouse_id')->paginate(12)->withQueryString();

        // KPI aggregates across all stock
        $all = WarehouseStock::with('material')->get();
        $byCategory = $all->groupBy(fn ($s) => $s->material->category)
            ->map(fn ($g) => $g->sum('quantity'));

        return view('inventory.index', [
            'stocks' => $stocks,
            'statusFilter' => $status,
            'warehouses' => Warehouse::orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'kpi' => [
                'aluminium' => $byCategory->get('Aluminium', 0),
                'kaca' => $byCategory->get('Kaca', 0),
                'aksesori' => $byCategory->get('Aksesori', 0),
                'critical' => $all->filter->isLow()->count(),
                'untagged' => $all->whereNull('location_tag')->count(),
                'total_value' => $all->sum(fn ($s) => (float) $s->quantity * (float) ($s->material->purchase_price ?? 0)),
                'total_items' => $all->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sku' => 'required|string|max:50|unique:materials,sku',
            'name' => 'required|string|max:150',
            'unit' => 'required|string|max:20',
            'category' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'quantity' => 'nullable|numeric|min:0',
            'location_tag' => 'nullable|string|max:50',
        ]);

        $material = Material::create([
            'sku' => $data['sku'],
            'name' => $data['name'],
            'unit' => $data['unit'],
            'category' => $data['category'],
            'purchase_price' => $data['purchase_price'],
            'min_stock' => $data['min_stock'],
        ]);

        if (! empty($data['warehouse_id']) && ! empty($data['quantity'])) {
            WarehouseStock::create([
                'warehouse_id' => $data['warehouse_id'],
                'material_id' => $material->id,
                'quantity' => $data['quantity'],
                'location_tag' => $data['location_tag'] ?? null,
            ]);
        }

        return redirect()->route('inventory.index')->with('success', "Material {$material->sku} berhasil ditambahkan.");
    }

    public function tagLocation(Request $request, WarehouseStock $stock): RedirectResponse
    {
        $data = $request->validate(['location_tag' => 'required|string|max:50']);
        $stock->update(['location_tag' => $data['location_tag']]);

        return back()->with('success', 'Lokasi rak berhasil ditandai.');
    }
}
