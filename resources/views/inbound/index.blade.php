@extends('layouts.app')

@section('title', 'Barang Masuk')
@section('breadcrumb', 'Gudang · Barang Masuk')
@section('page-title', 'Barang Masuk')

@section('topbar-actions')
    <a href="{{ route('inbound.create') }}" class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-slate-800">
        <x-icon name="plus" class="w-4 h-4" /> Catat Barang Masuk
    </a>
@endsection

@php
    $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
@endphp

@section('content')
<x-card title="Riwayat Penerimaan Barang">
    <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
        <div class="flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 flex-1 min-w-48">
            <x-icon name="search" class="w-4 h-4 text-slate-400" />
            <input name="search" value="{{ request('search') }}" placeholder="Cari kode transaksi…" class="bg-transparent text-sm outline-none flex-1">
        </div>
        <select name="warehouse" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
            <option value="">Gudang: Semua</option>
            @foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('warehouse')==$w->id)>{{ $w->name }}</option>@endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm rounded-lg border border-slate-200 px-3 py-2" title="Dari tanggal">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm rounded-lg border border-slate-200 px-3 py-2" title="Sampai tanggal">
        <button class="bg-amber-500 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-amber-400">Filter</button>
        @if(request()->hasAny(['search','warehouse','date_from','date_to']))
            <a href="{{ route('inbound.index') }}" class="text-xs text-slate-400 hover:text-red-500">✕ Reset</a>
        @endif
    </form>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                    <th class="py-2">Kode</th><th>Tanggal</th><th>Gudang</th><th>Total Qty</th><th>Dicatat Oleh</th><th class="text-right">Nilai</th><th>Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($inbounds as $tx)
                    <tr>
                        <td class="py-2.5 font-mono text-xs text-slate-600">{{ $tx->code }}</td>
                        <td class="text-slate-500">{{ $tx->created_at->format('d M Y H:i') }}</td>
                        <td class="text-slate-600">{{ \Illuminate\Support\Str::after($tx->toWarehouse?->name ?? '—', '— ') }}</td>
                        <td class="text-slate-700 font-medium">{{ $qty($tx->quantity) }}</td>
                        <td class="text-slate-600">{{ $tx->creator->full_name }}</td>
                        <td class="text-right text-slate-700">{{ $rp($tx->amount) }}</td>
                        <td class="text-slate-400 text-xs max-w-xs truncate">{{ $tx->notes ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada barang masuk dicatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $inbounds->links() }}</div>
</x-card>
@endsection
