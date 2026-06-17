@extends('layouts.app')

@section('title', 'Purchase Order')
@section('breadcrumb', 'Supplier · Purchase Order')
@section('page-title', 'Purchase Order')

@section('topbar-actions')
    <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-slate-800">
        <x-icon name="plus" class="w-4 h-4" /> Buat PO Baru
    </a>
@endsection

@php $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('purchase-orders.index') }}" @class(['px-3 py-1.5 rounded-lg text-sm font-medium', 'bg-slate-900 text-white' => ! $statusFilter, 'bg-white border border-slate-200 text-slate-600' => $statusFilter])>Semua</a>
        @foreach(['draft'=>'Draft','pending'=>'Pending','approved'=>'Approved','ordered'=>'Ordered','received'=>'Received','rejected'=>'Rejected'] as $k=>$label)
            <a href="{{ route('purchase-orders.index', ['status'=>$k]) }}" @class(['px-3 py-1.5 rounded-lg text-sm font-medium', 'bg-slate-900 text-white' => $statusFilter===$k, 'bg-white border border-slate-200 text-slate-600' => $statusFilter!==$k])>
                {{ $label }} <span class="opacity-60">{{ $counts[$k] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                    <th class="py-2">No. PO</th><th>Supplier</th><th>Item</th><th>Dibuat Oleh</th><th>Status</th><th class="text-right">Total Estimasi</th><th>Dibutuhkan</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($orders as $po)
                        <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('purchase-orders.show', $po) }}'">
                            <td class="py-2.5 font-mono text-xs text-slate-600">{{ $po->po_number }}</td>
                            <td class="font-medium text-slate-700">{{ $po->supplier->name }}</td>
                            <td class="text-slate-500">{{ $po->items_count }} item</td>
                            <td class="text-slate-500">{{ $po->creator->full_name }}</td>
                            <td><x-status-pill :status="$po->status" /></td>
                            <td class="text-right font-semibold text-slate-700">{{ $rp($po->total_estimated) }}</td>
                            <td class="text-slate-400 text-xs">{{ $po->needed_date?->format('d M Y') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada purchase order.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </x-card>
</div>
@endsection
