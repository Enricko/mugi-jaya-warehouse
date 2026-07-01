<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Notification;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = PurchaseOrder::with(['supplier', 'creator', 'approver'])->withCount('items');

        if ($search = $request->get('search')) {
            $query->where('po_number', 'like', "%{$search}%");
        }
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }
        if ($supplier = $request->get('supplier')) {
            $query->where('supplier_id', $supplier);
        }
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('needed_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('needed_date', '<=', $dateTo);
        }

        return view('purchase-orders.index', [
            'orders' => $query->latest()->paginate(15)->withQueryString(),
            'statusFilter' => $status ?? null,
            'counts' => PurchaseOrder::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status'),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('purchase-orders.create', [
            'suppliers' => Supplier::where('is_active', true)->orderBy('name')->get(),
            'warehouses' => Warehouse::where('is_active', true)->orderBy('name')->get(),
            'materials' => Material::orderBy('name')->get(),
            'materialsJson' => Material::orderBy('name')->get()->map(fn ($m) => [
                'id' => $m->id, 'name' => $m->name, 'sku' => $m->sku, 'unit' => $m->unit, 'price' => (float) $m->purchase_price,
            ])->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'needed_date' => 'nullable|date',
            'submit' => 'nullable|in:draft,pending',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $total = collect($data['items'])->sum(fn ($i) => (float) $i['quantity'] * (float) $i['unit_price']);
        $status = ($data['submit'] ?? 'pending') === 'draft' ? 'draft' : 'pending';

        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . now()->year . '-' . str_pad((string) (PurchaseOrder::count() + 151), 4, '0', STR_PAD_LEFT),
            'supplier_id' => $data['supplier_id'],
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'created_by' => $request->user()->id,
            'status' => $status,
            'total_estimated' => $total,
            'needed_date' => $data['needed_date'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            $po->items()->create([
                'material_id' => $item['material_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => (float) $item['quantity'] * (float) $item['unit_price'],
            ]);
        }

        if ($status === 'pending') {
            foreach (User::where('role', 'owner')->pluck('id') as $id) {
                Notification::create([
                    'user_id' => $id, 'title' => 'PO Menunggu Approval',
                    'message' => "{$po->po_number} dari " . $po->supplier->name . ' menunggu persetujuan Anda.',
                    'type' => 'warning', 'module' => 'Supplier', 'is_read' => false,
                ]);
            }
        }

        return redirect()->route('purchase-orders.show', $po)->with('success', "PO {$po->po_number} berhasil dibuat.");
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'approver', 'items.material']);

        return view('purchase-orders.show', ['po' => $purchaseOrder]);
    }

    public function approve(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'PO ini tidak dapat disetujui.');
        }

        $purchaseOrder->update(['status' => 'approved', 'approved_by' => $request->user()->id]);

        foreach (User::where('role', 'kepala_gudang')->pluck('id') as $id) {
            Notification::create([
                'user_id' => $id, 'title' => 'PO Disetujui',
                'message' => "{$purchaseOrder->po_number} telah disetujui Owner. Dokumen PO siap diunduh.",
                'type' => 'info', 'module' => 'Supplier', 'is_read' => false,
            ]);
        }

        return back()->with('success', "PO {$purchaseOrder->po_number} disetujui. Dokumen siap diunduh.");
    }

    public function reject(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|min:10|max:500']);

        if ($purchaseOrder->status !== 'pending') {
            return back()->with('error', 'PO ini tidak dapat ditolak.');
        }

        $purchaseOrder->update(['status' => 'rejected', 'approved_by' => $request->user()->id]);

        return back()->with('success', "PO {$purchaseOrder->po_number} ditolak.");
    }

    /** Print-friendly PO document (print to PDF from the browser). */
    public function document(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'creator', 'approver', 'items.material']);

        return view('purchase-orders.document', ['po' => $purchaseOrder]);
    }
}
