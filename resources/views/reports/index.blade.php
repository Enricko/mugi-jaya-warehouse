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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Stok per gudang --}}
        <x-card>
            <x-slot:actions><a href="{{ route('reports.export', ['type'=>'stock']) }}" class="text-xs text-amber-600 font-medium inline-flex items-center gap-1"><x-icon name="export" class="w-3.5 h-3.5" /> CSV</a></x-slot:actions>
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
            <x-slot:actions><a href="{{ route('reports.export', ['type'=>'valuation']) }}" class="text-xs text-amber-600 font-medium inline-flex items-center gap-1"><x-icon name="export" class="w-3.5 h-3.5" /> CSV</a></x-slot:actions>
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
            <x-slot:actions><a href="{{ route('reports.export', ['type'=>'consumption']) }}" class="text-xs text-amber-600 font-medium inline-flex items-center gap-1"><x-icon name="export" class="w-3.5 h-3.5" /> CSV</a></x-slot:actions>
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
            <x-slot:actions><a href="{{ route('reports.export', ['type'=>'driver']) }}" class="text-xs text-amber-600 font-medium inline-flex items-center gap-1"><x-icon name="export" class="w-3.5 h-3.5" /> CSV</a></x-slot:actions>
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
