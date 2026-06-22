@extends('layouts.app')

@section('title', $po->po_number)
@section('breadcrumb', 'Supplier · Purchase Order')
@section('page-title', $po->po_number)

@section('topbar-actions')
    @if(in_array($po->status, ['approved','ordered','received']))
        <a href="{{ route('purchase-orders.document', $po) }}" target="_blank" class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-slate-800">
            <x-icon name="export" class="w-4 h-4" /> Dokumen PO (PDF)
        </a>
    @endif
@endsection

@php
    $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-slate-800">{{ $po->supplier->name }}</h3>
                    <p class="text-xs text-slate-400">{{ $po->supplier->city }} · {{ $po->supplier->contact_phone }}</p>
                </div>
                <x-status-pill :status="$po->status" />
            </div>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                    <th class="py-2">Material</th><th class="text-right">Qty</th><th class="text-right">Harga Satuan</th><th class="text-right">Subtotal</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($po->items as $it)
                        <tr>
                            <td class="py-2"><span class="font-mono text-xs text-slate-500">{{ $it->material->sku }}</span> {{ $it->material->name }}</td>
                            <td class="text-right">{{ $qty($it->quantity) }} {{ $it->material->unit }}</td>
                            <td class="text-right text-slate-600">{{ $rp($it->unit_price) }}</td>
                            <td class="text-right font-medium text-slate-700">{{ $rp($it->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr class="border-t border-slate-200"><td colspan="3" class="py-3 text-right font-semibold text-slate-600">Total Estimasi</td><td class="text-right font-bold text-slate-800">{{ $rp($po->total_estimated) }}</td></tr></tfoot>
            </table>
        </x-card>
    </div>

    <div class="space-y-4">
        <x-card title="Detail PO">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-400">Dibuat oleh</dt><dd class="text-slate-700">{{ $po->creator->full_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Tanggal dibuat</dt><dd class="text-slate-700">{{ $po->created_at->format('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Gudang Tujuan</dt><dd class="text-slate-700">{{ $po->warehouse?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Dibutuhkan</dt><dd class="text-slate-700">{{ $po->needed_date?->format('d M Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Approver</dt><dd class="text-slate-700">{{ $po->approver?->full_name ?? '—' }}</dd></div>
            </dl>
        </x-card>

        @if($po->status === 'pending' && auth()->user()->role === 'owner')
            <x-card title="Approval">
                <form method="POST" action="{{ route('purchase-orders.approve', $po) }}" class="mb-2">@csrf
                    <button class="w-full inline-flex items-center justify-center gap-1.5 bg-green-600 text-white text-sm font-semibold rounded-lg py-2.5 hover:bg-green-700"><x-icon name="check" class="w-4 h-4" /> Setujui PO</button>
                </form>
                <form method="POST" action="{{ route('purchase-orders.reject', $po) }}" class="space-y-2">@csrf
                    <textarea name="reason" required minlength="10" rows="2" placeholder="Alasan penolakan (min 10 karakter)…" class="w-full text-sm rounded-lg border border-slate-200 px-3 py-2 outline-none focus:ring-2 focus:ring-red-400"></textarea>
                    <button class="w-full bg-red-600 text-white text-sm font-semibold rounded-lg py-2 hover:bg-red-700">Tolak PO</button>
                </form>
            </x-card>
        @elseif($po->status === 'pending')
            <x-card><p class="text-sm text-slate-400 text-center py-2">Menunggu persetujuan Owner.</p></x-card>
        @endif
    </div>
</div>
@endsection
