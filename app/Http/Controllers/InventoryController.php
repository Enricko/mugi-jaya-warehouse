<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Notification;
use App\Models\PoItem;
use App\Models\Project;
use App\Models\ShipmentItem;
use App\Models\Transaction;
use App\Models\User;
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

        // Materials that exist in the catalogue but are not yet stocked in any
        // warehouse. Without this they would be saved (and audit-logged) yet
        // never appear on the inventory page — surfacing them here lets the user
        // assign them to a gudang. Respects the text/category filters.
        $stocklessQuery = Material::doesntHave('stocks');
        if ($search) {
            $stocklessQuery->where(fn ($m) => $m->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
        }
        if ($cat) {
            $stocklessQuery->where('category', $cat);
        }
        $stocklessMaterials = $stocklessQuery->orderBy('name')->get();

        // KPI aggregates across all stock
        $all = WarehouseStock::with('material')->get();
        $byCategory = $all->groupBy(fn ($s) => $s->material->category)
            ->map(fn ($g) => $g->sum('quantity'));

        return view('inventory.index', [
            'stocks' => $stocks,
            'stocklessMaterials' => $stocklessMaterials,
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

        // When a warehouse is chosen, always create the stock row (qty may be 0)
        // so the material is immediately linked to a gudang and visible.
        if (! empty($data['warehouse_id'])) {
            WarehouseStock::create([
                'warehouse_id' => $data['warehouse_id'],
                'material_id' => $material->id,
                'quantity' => $data['quantity'] ?? 0,
                'location_tag' => $data['location_tag'] ?? null,
            ]);

            return redirect()->route('inventory.index')->with('success', "Material {$material->sku} berhasil ditambahkan & disimpan ke gudang.");
        }

        return redirect()->route('inventory.index')
            ->with('success', "Material {$material->sku} dibuat. Material belum punya stok — tambahkan ke gudang di bagian \"Material Belum Punya Stok\".");
    }

    /**
     * Assign / add stock of an existing material into a warehouse. Used to
     * stock a freshly-created catalogue material, or to top-up an existing row.
     */
    public function addStock(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|numeric|min:0',
            'location_tag' => 'nullable|string|max:50',
        ]);

        $stock = WarehouseStock::firstOrNew([
            'warehouse_id' => $data['warehouse_id'],
            'material_id' => $data['material_id'],
            'project_id' => null,
        ]);
        $stock->quantity = (float) ($stock->quantity ?? 0) + (float) $data['quantity'];
        if (! empty($data['location_tag'])) {
            $stock->location_tag = $data['location_tag'];
        }
        $stock->save();

        $material = Material::find($data['material_id']);

        return redirect()->route('inventory.index')->with('success', "Stok {$material->sku} berhasil disimpan ke gudang.");
    }

    public function tagLocation(Request $request, WarehouseStock $stock): RedirectResponse
    {
        $data = $request->validate(['location_tag' => 'required|string|max:50']);
        $stock->update(['location_tag' => $data['location_tag']]);

        return back()->with('success', 'Lokasi rak berhasil ditandai.');
    }

    /**
     * Add quantity to an existing stock row (works on project-tagged rows too,
     * since it binds the specific row rather than the general-stock pool).
     */
    public function restock(Request $request, WarehouseStock $stock): RedirectResponse
    {
        $data = $request->validate(['quantity' => 'required|numeric|min:0.01']);
        $stock->quantity = (float) $stock->quantity + (float) $data['quantity'];
        $stock->save();

        return back()->with('success', "Stok {$stock->material->sku} ditambah {$this->qty($data['quantity'])} {$stock->material->unit}.");
    }

    /**
     * Set an exact quantity for a stock row (stock-opname / koreksi). The change
     * is auto-audited by the Auditable trait; the reason is persisted as a
     * notification so the "why" survives next to the numeric before/after.
     */
    public function adjust(Request $request, WarehouseStock $stock): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'reason' => 'required|string|min:3|max:300',
        ]);

        $old = (float) $stock->quantity;
        $stock->quantity = (float) $data['quantity'];
        $stock->save();

        foreach (User::whereIn('role', ['owner', 'kepala_gudang'])->pluck('id') as $id) {
            Notification::create([
                'user_id' => $id,
                'title' => 'Koreksi Stok',
                'message' => "{$stock->material->name} di {$stock->warehouse->name}: {$this->qty($old)} → {$this->qty($data['quantity'])} {$stock->material->unit}. Alasan: {$data['reason']}",
                'type' => 'warning',
                'module' => 'Warehouse',
                'is_read' => false,
            ]);
        }

        return back()->with('success', "Stok {$stock->material->sku} dikoreksi menjadi {$this->qty($data['quantity'])} {$stock->material->unit}.");
    }

    /** Edit a material's catalogue details (affects every stock row of it). */
    public function updateMaterial(Request $request, Material $material): RedirectResponse
    {
        $data = $request->validate([
            'sku' => 'required|string|max:50|unique:materials,sku,' . $material->id,
            'name' => 'required|string|max:150',
            'unit' => 'required|string|max:20',
            'category' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $material->update($data);

        return back()->with('success', "Material {$material->sku} diperbarui.");
    }

    /** Delete a single stock row — removes the material from that one gudang. */
    public function destroyStock(WarehouseStock $stock): RedirectResponse
    {
        $label = $stock->material->sku;
        $stock->delete();

        return back()->with('success', "Baris stok {$label} dihapus dari gudang.");
    }

    /**
     * Delete a material from the catalogue. Blocked when it is referenced by any
     * transaction, PO item or shipment item so historical records stay intact;
     * otherwise it is removed along with its (cascade-deleted) stock rows.
     */
    public function destroyMaterial(Material $material): RedirectResponse
    {
        $referenced = Transaction::where('material_id', $material->id)->exists()
            || PoItem::where('material_id', $material->id)->exists()
            || ShipmentItem::where('material_id', $material->id)->exists();

        if ($referenced) {
            return back()->with('error', "Material {$material->sku} tidak bisa dihapus — sudah dipakai di transaksi, PO, atau pengiriman.");
        }

        $sku = $material->sku;
        $material->delete();

        return back()->with('success', "Material {$sku} dihapus dari katalog.");
    }

    /** Format a decimal quantity without trailing zeros (e.g. 8.00 → "8"). */
    private function qty(float|string $v): string
    {
        return rtrim(rtrim(number_format((float) $v, 2), '0'), '.');
    }
}
