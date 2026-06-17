@extends('layouts.app')

@section('title', $shipment->code)
@section('breadcrumb', 'Pengiriman · Detail')
@section('page-title', $shipment->code . ' — ' . $shipment->project->name)

@php
    $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
    $steps = ['confirmed' => 'Confirmed', 'in_transit' => 'In Transit', 'delivered' => 'Delivered'];
    $order = ['draft' => 0, 'confirmed' => 1, 'in_transit' => 2, 'delivered' => 3, 'problem' => 2];
    $current = $order[$shipment->status] ?? 0;
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <x-card>
            <div class="flex items-center justify-between mb-5">
                <h3 class="font-semibold text-slate-800">Status Pengiriman</h3>
                <x-status-pill :status="$shipment->status" />
            </div>
            {{-- Timeline --}}
            <div class="flex items-center">
                @foreach($steps as $key => $label)
                    @php $done = $current >= $order[$key]; @endphp
                    <div class="flex flex-col items-center">
                        <div @class(['w-9 h-9 rounded-full grid place-items-center', 'bg-green-500 text-white' => $done, 'bg-slate-200 text-slate-400' => ! $done])>
                            @if($done)<x-icon name="check" class="w-5 h-5" />@else <span class="text-xs">{{ $loop->iteration }}</span>@endif
                        </div>
                        <span class="text-[11px] mt-1 {{ $done ? 'text-slate-700 font-medium' : 'text-slate-400' }}">{{ $label }}</span>
                    </div>
                    @if(! $loop->last)<div @class(['flex-1 h-0.5 mx-1', 'bg-green-500' => $current > $order[$key], 'bg-slate-200' => $current <= $order[$key]])></div>@endif
                @endforeach
            </div>
            @if($shipment->status === 'problem')
                <div class="mt-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2 flex items-center gap-2">
                    <x-icon name="alert" class="w-4 h-4" /> Pengiriman ini dilaporkan bermasalah oleh driver.
                </div>
            @endif
        </x-card>

        <x-card title="Muatan" :subtitle="$shipment->items->count() . ' item'">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100"><th class="py-2">SKU</th><th>Material</th><th class="text-right">Kuantitas</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($shipment->items as $it)
                        <tr><td class="py-2 font-mono text-xs text-slate-500">{{ $it->material->sku }}</td><td class="text-slate-700">{{ $it->material->name }}</td><td class="text-right font-medium">{{ $qty($it->quantity) }} {{ $it->material->unit }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </x-card>

        @if($shipment->receiver_name || $shipment->receiver_signature)
            <x-card title="Bukti Serah Terima">
                <p class="text-sm text-slate-600">Diterima oleh: <span class="font-medium">{{ $shipment->receiver_name ?? '—' }}</span>
                    @if($shipment->delivered_at) · {{ $shipment->delivered_at->format('d M Y H:i') }} @endif</p>
                @if($shipment->receiver_signature)
                    <img src="{{ $shipment->receiver_signature }}" alt="ttd" class="mt-2 h-24 border border-slate-200 rounded-lg bg-white">
                @endif
            </x-card>
        @endif
    </div>

    <div class="space-y-4">
        <x-card title="Detail">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-400">Driver</dt><dd class="text-slate-700 font-medium">{{ $shipment->driver->full_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Kendaraan</dt><dd class="text-slate-700">{{ $shipment->vehicle_plate }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Gudang Asal</dt><dd class="text-slate-700">{{ \Illuminate\Support\Str::after($shipment->warehouse->name, '— ') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Tujuan</dt><dd class="text-slate-700 text-right">{{ $shipment->project->location }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Dibuat</dt><dd class="text-slate-700">{{ $shipment->created_at->format('d M Y H:i') }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Ubah Status">
            <form method="POST" action="{{ route('shipments.status', $shipment) }}" class="space-y-2">
                @csrf
                <select name="status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    @foreach(['confirmed'=>'Confirmed','in_transit'=>'In Transit','delivered'=>'Delivered','problem'=>'Problem'] as $k=>$label)
                        <option value="{{ $k }}" @selected($shipment->status===$k)>{{ $label }}</option>
                    @endforeach
                </select>
                <button class="w-full bg-slate-900 text-white text-sm font-semibold rounded-lg py-2 hover:bg-slate-800">Perbarui Status</button>
            </form>
        </x-card>
    </div>
</div>
@endsection
