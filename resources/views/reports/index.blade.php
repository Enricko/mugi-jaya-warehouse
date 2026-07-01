@extends('layouts.app')

@section('title', 'Laporan')
@section('breadcrumb', 'Reporting · Laporan')
@section('page-title', 'Laporan')

@php
    $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
@endphp

@section('content')
<div class="space-y-4">
    <x-card>
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-slate-600">Periode:</span>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="text-sm rounded-lg border border-slate-200 px-3 py-2" title="Dari tanggal">
            <span class="text-xs text-slate-400">s/d</span>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="text-sm rounded-lg border border-slate-200 px-3 py-2" title="Sampai tanggal">
            <select name="warehouse" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Gudang: Semua</option>
                @foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(($filters['warehouse'] ?? '')==$w->id)>{{ $w->name }}</option>@endforeach
            </select>
            <button class="bg-amber-500 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-amber-400">Filter</button>
            @if(($filters['date_from'] ?? '') || ($filters['date_to'] ?? '') || ($filters['warehouse'] ?? ''))
                <a href="{{ route('reports.index') }}" class="text-xs text-slate-400 hover:text-red-500">✕ Reset</a>
            @endif
        </form>
    </x-card>

    @php $exportParams = array_filter($filters ?? []); @endphp
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Stok per gudang --}}
        <x-card>
            <x-slot:actions><a href="{{ route('reports.export', array_merge(['type'=>'stock'], $exportParams)) }}" class="text-xs text-amber-600 font-medium inline-flex items-center gap-1"><x-icon name="export" class="w-3.5 h-3.5" /> CSV</a></x-slot:actions>
            <h3 class="font-semibold text-slate-800 mb-3">Laporan Stok per Gudang</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] uppercase text-slate-400 border-b border-slate-100"><th class="py-1.5">Gudang</th><th class="text-right">Item</th><th class="text-right">Qty</th><th class="text-right">Nilai</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($stock as $r)
                        <tr><td class="py-2">{{ \Illuminate\Support\Str::after($r['name'], '— ') }}</td><td class="text-right">{{ $r['items'] }}</td><td class="text-right">{{ $qty($r['qty']) }}</td><td class="text-right text-slate-600">{{ $rp($r['value']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>

        {{-- Nilai inventory --}}
        <x-card>
            <x-slot:actions><a href="{{ route('reports.export', array_merge(['type'=>'valuation'], $exportParams)) }}" class="text-xs text-amber-600 font-medium inline-flex items-center gap-1"><x-icon name="export" class="w-3.5 h-3.5" /> CSV</a></x-slot:actions>
            <h3 class="font-semibold text-slate-800 mb-3">Laporan Nilai Inventory</h3>
            <div class="space-y-2">
                @foreach($valuation as $r)
                    <div class="flex items-center justify-between text-sm border-b border-slate-50 pb-2">
                        <span>{{ \Illuminate\Support\Str::after($r['name'], '— ') }}</span>
                        <span class="font-semibold text-slate-700">{{ $rp($r['value']) }}</span>
                    </div>
                @endforeach
                <div class="flex items-center justify-between pt-1">
                    <span class="font-semibold text-slate-700">Grand Total Aset</span>
                    <span class="font-bold text-amber-600">{{ $rp($grandTotal) }}</span>
                </div>
            </div>
        </x-card>

        {{-- Konsumsi material --}}
        <x-card>
            <x-slot:actions><a href="{{ route('reports.export', array_merge(['type'=>'consumption'], $exportParams)) }}" class="text-xs text-amber-600 font-medium inline-flex items-center gap-1"><x-icon name="export" class="w-3.5 h-3.5" /> CSV</a></x-slot:actions>
            <h3 class="font-semibold text-slate-800 mb-3">Laporan Konsumsi Material</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] uppercase text-slate-400 border-b border-slate-100"><th class="py-1.5">Material</th><th class="text-right">Qty</th><th class="text-right">Nilai</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($consumption as $r)
                        <tr><td class="py-2">{{ $r['name'] }}</td><td class="text-right">{{ $qty($r['qty']) }}</td><td class="text-right text-slate-600">{{ $rp($r['value']) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-4 text-center text-slate-400">Belum ada data konsumsi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </x-card>

        {{-- Riwayat driver --}}
        <x-card>
            <x-slot:actions><a href="{{ route('reports.export', array_merge(['type'=>'driver'], $exportParams)) }}" class="text-xs text-amber-600 font-medium inline-flex items-center gap-1"><x-icon name="export" class="w-3.5 h-3.5" /> CSV</a></x-slot:actions>
            <h3 class="font-semibold text-slate-800 mb-3">Laporan Riwayat Driver</h3>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] uppercase text-slate-400 border-b border-slate-100"><th class="py-1.5">Driver</th><th class="text-right">Total</th><th class="text-right">Delivered</th><th class="text-right">Problem</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($drivers as $r)
                        <tr><td class="py-2">{{ $r['name'] }}</td><td class="text-right">{{ $r['total'] }}</td><td class="text-right text-green-600">{{ $r['delivered'] }}</td><td class="text-right text-red-500">{{ $r['problem'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>
    </div>
</div>
@endsection
