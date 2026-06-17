@extends('layouts.app')

@section('title', $project->name)
@section('breadcrumb', 'Proyek · Detail')
@section('page-title', $project->name)

@php $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.'); @endphp

@section('content')
<div class="space-y-4">
    <x-card>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div><p class="text-xs text-slate-400 uppercase">Klien</p><p class="text-sm text-slate-700">{{ $project->client_name }}</p></div>
            <div><p class="text-xs text-slate-400 uppercase">Lokasi</p><p class="text-sm text-slate-700">{{ $project->location }}</p></div>
            <div><p class="text-xs text-slate-400 uppercase">Status</p><x-status-pill :status="$project->status" /></div>
            <div><p class="text-xs text-slate-400 uppercase">Periode</p><p class="text-sm text-slate-700">{{ $project->start_date?->format('d M Y') ?? '—' }} – {{ $project->end_date?->format('d M Y') ?? '—' }}</p></div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-card title="Material Teralokasi" :subtitle="$stocks->count() . ' item'">
            <div class="space-y-2">
                @forelse($stocks as $s)
                    <div class="flex items-center justify-between text-sm border-b border-slate-50 pb-2">
                        <div><span class="font-medium text-slate-700">{{ $s->material->name }}</span>
                            <span class="text-xs text-slate-400">· {{ \Illuminate\Support\Str::after($s->warehouse->name, '— ') }}</span></div>
                        <span class="font-semibold text-slate-700">{{ $qty($s->quantity) }} {{ $s->material->unit }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada material teralokasi.</p>
                @endforelse
            </div>
        </x-card>

        <x-card title="Konsumsi Material" :subtitle="$consumption->count() . ' transaksi'">
            <div class="space-y-2">
                @forelse($consumption as $c)
                    <div class="flex items-center justify-between text-sm border-b border-slate-50 pb-2">
                        <div><span class="font-medium text-slate-700">{{ $c->material?->name }}</span>
                            <span class="text-xs text-slate-400">· {{ $c->created_at->format('d M Y') }}</span></div>
                        <span class="font-semibold text-slate-700">{{ $qty($c->quantity) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada konsumsi tercatat.</p>
                @endforelse
            </div>
        </x-card>
    </div>

    <x-card title="Pengiriman ke Proyek Ini" :subtitle="$shipments->count() . ' pengiriman'">
        <div class="space-y-2">
            @forelse($shipments as $sh)
                <a href="{{ route('shipments.show', $sh) }}" class="flex items-center justify-between text-sm border-b border-slate-50 pb-2 hover:bg-slate-50 px-1 rounded">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs text-slate-500">{{ $sh->code }}</span>
                        <span class="text-slate-600">{{ $sh->driver->full_name }} · {{ $sh->vehicle_plate }}</span>
                    </div>
                    <x-status-pill :status="$sh->status" />
                </a>
            @empty
                <p class="text-sm text-slate-400">Belum ada pengiriman.</p>
            @endforelse
        </div>
    </x-card>
</div>
@endsection
