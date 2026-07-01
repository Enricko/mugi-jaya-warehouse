<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function index(Request $request): View
    {
        $pending = Transaction::with(['fromWarehouse', 'toWarehouse', 'material', 'creator', 'approver'])
            ->where('type', 'transfer')->where('status', 'pending')->latest()->get();

        $historyQuery = Transaction::with(['fromWarehouse', 'toWarehouse', 'material', 'creator', 'approver'])
            ->where('type', 'transfer')
            ->whereIn('status', ['approved', 'rejected', 'completed']);

        if ($search = $request->get('search')) {
            $historyQuery->where('code', 'like', "%{$search}%");
        }
        if ($status = $request->get('status')) {
            $historyQuery->where('status', $status);
        }
        if ($fromWh = $request->get('from_warehouse')) {
            $historyQuery->where('from_warehouse_id', $fromWh);
        }
        if ($toWh = $request->get('to_warehouse')) {
            $historyQuery->where('to_warehouse_id', $toWh);
        }

        return view('transfers.index', [
            'pending' => $pending,
            'history' => $historyQuery->latest()->paginate(15)->withQueryString(),
            'warehouses' => Warehouse::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('transfers.create', [
            'warehouses' => Warehouse::orderBy('name')->get(),
            'materials' => Material::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:500',
        ]);

        $material = Material::findOrFail($data['material_id']);
        $amount = (float) $data['quantity'] * (float) $material->purchase_price;

        $transfer = Transaction::create([
            'code' => 'TRF-' . str_pad((string) (Transaction::where('type', 'transfer')->count() + 92), 4, '0', STR_PAD_LEFT),
            'type' => 'transfer',
            'status' => 'pending',
            'from_warehouse_id' => $data['from_warehouse_id'],
            'to_warehouse_id' => $data['to_warehouse_id'],
            'material_id' => $data['material_id'],
            'quantity' => $data['quantity'],
            'created_by' => $request->user()->id,
            'amount' => $amount,
            'notes' => $data['notes'] ?? null,
        ]);

        $this->notifyApprovers('Transfer Menunggu Approval', "{$transfer->code} dibuat dan menunggu persetujuan.", 'warning');

        return redirect()->route('transfers.index')->with('success', "Request transfer {$transfer->code} dibuat dan menunggu approval.");
    }

    public function approve(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->type !== 'transfer' || $transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini tidak dapat disetujui.');
        }

        $source = WarehouseStock::firstOrNew([
            'warehouse_id' => $transaction->from_warehouse_id,
            'material_id' => $transaction->material_id,
            'project_id' => null,
        ]);

        if ((float) ($source->quantity ?? 0) < (float) $transaction->quantity) {
            return back()->with('error', 'Stok di gudang asal tidak mencukupi untuk transfer ini.');
        }

        DB::transaction(function () use ($transaction, $source, $request) {
            $source->quantity = (float) $source->quantity - (float) $transaction->quantity;
            $source->save();

            $dest = WarehouseStock::firstOrNew([
                'warehouse_id' => $transaction->to_warehouse_id,
                'material_id' => $transaction->material_id,
                'project_id' => null,
            ]);
            $dest->quantity = (float) ($dest->quantity ?? 0) + (float) $transaction->quantity;
            $dest->save();

            $transaction->update(['status' => 'approved', 'approved_by' => $request->user()->id]);
        });

        $this->notifyApprovers('Transfer Disetujui', "{$transaction->code} telah disetujui dan stok dipindahkan.", 'info');

        return back()->with('success', "Transfer {$transaction->code} disetujui. Stok telah dipindahkan.");
    }

    public function reject(Request $request, Transaction $transaction): RedirectResponse
    {
        $data = $request->validate(['reason' => 'required|string|min:10|max:500']);

        if ($transaction->type !== 'transfer' || $transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini tidak dapat ditolak.');
        }

        $transaction->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'notes' => trim(($transaction->notes ? $transaction->notes . ' — ' : '') . 'DITOLAK: ' . $data['reason']),
        ]);

        return back()->with('success', "Transfer {$transaction->code} ditolak.");
    }

    private function notifyApprovers(string $title, string $message, string $type): void
    {
        $recipients = User::whereIn('role', ['owner', 'kepala_gudang'])->pluck('id');
        foreach ($recipients as $id) {
            Notification::create([
                'user_id' => $id, 'title' => $title, 'message' => $message,
                'type' => $type, 'module' => 'Warehouse', 'is_read' => false,
            ]);
        }
    }
}
