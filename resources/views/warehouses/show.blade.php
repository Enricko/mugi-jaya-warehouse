@extends('layouts.app')

@section('title', $warehouse->name)
@section('breadcrumb', 'Gudang · Detail')
@section('page-title', $warehouse->name)

@php
    $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
@endphp

@section('content')
<div class="space-y-4">
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div><p class="text-xs text-slate-400 uppercase">Alamat</p><p class="text-sm text-slate-700">{{ $warehouse->address }}</p></div>
            <div><p class="text-xs text-slate-400 uppercase">Mandor</p><p class="text-sm text-slate-700">{{ $warehouse->mandor?->full_name ?? '—' }}</p></div>
            <div><p class="text-xs text-slate-400 uppercase">Koordinat</p><p class="text-sm text-slate-700">{{ $warehouse->latitude }}, {{ $warehouse->longitude }}</p></div>
            <div><p class="text-xs text-slate-400 uppercase">Status</p><p class="text-sm">{{ $warehouse->is_active ? 'Operasional' : 'Nonaktif' }}</p></div>
        </div>
    </x-card>

    <x-card title="Stok Material" :subtitle="$stocks->count() . ' item'">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                        <th class="py-2">SKU</th><th>Material</th><th>Lokasi Rak</th><th>Proyek</th><th class="text-right">Kuantitas</th><th class="text-right">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stocks as $s)
                        <tr class="{{ $s->isLow() ? 'bg-red-50/50' : '' }}">
                            <td class="py-2.5 font-mono text-xs text-slate-500">{{ $s->material->sku }}</td>
                            <td class="font-medium text-slate-700">
                                @if($s->isLow())<x-icon name="alert" class="w-3.5 h-3.5 inline text-red-500" />@endif
                                {{ $s->material->name }}
                            </td>
                            <td>{{ $s->location_tag ?? '—' }}</td>
                            <td class="text-slate-500">{{ $s->project?->name ?? 'umum' }}</td>
                            <td class="text-right font-semibold {{ $s->isLow() ? 'text-red-600' : 'text-slate-700' }}">{{ $qty($s->quantity) }} {{ $s->material->unit }}</td>
                            <td class="text-right text-slate-600">{{ $rp($s->quantity * $s->material->purchase_price) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 text-center text-slate-400">Belum ada stok di gudang ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
