@extends('layouts.app')

@section('title', 'Dashboard Driver')
@section('breadcrumb', 'Beranda · Driver')
@section('page-title', 'Selamat Datang, ' . $user->full_name)

@section('content')
<div class="space-y-6">
    {{-- Welcome & Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-card class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500 text-slate-900 font-bold grid place-items-center text-lg shrink-0">
                {{ strtoupper(\Illuminate\Support\Str::substr($user->full_name, 0, 2)) }}
            </div>
            <div>
                <h2 class="text-lg font-bold text-slate-800">Siap mengantar, {{ $user->full_name }}?</h2>
                <p class="text-sm text-slate-500">Anda memiliki <strong class="text-slate-700">{{ $activeShipments->count() }}</strong> pengiriman aktif saat ini.</p>
            </div>
        </x-card>

        <x-card class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-500/10 text-green-600 grid place-items-center shrink-0">
                <x-icon name="check" class="w-6 h-6" />
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500">Selesai Hari Ini</p>
                <p class="text-2xl font-bold text-slate-800">{{ $todayDeliveriesCount }} <span class="text-sm font-normal text-slate-500">tujuan</span></p>
            </div>
        </x-card>
    </div>

    {{-- Active Shipments --}}
    <div class="space-y-3">
        <div class="flex items-center justify-between px-1">
            <h3 class="text-base font-bold text-slate-800">
                Pengiriman Aktif
            </h3>
        </div>

        @if($activeShipments->isEmpty())
            <x-card class="py-12 text-center border-dashed">
                <h4 class="text-slate-700 font-medium mb-1">Tidak ada pengiriman</h4>
                <p class="text-sm text-slate-500">Saat ini tidak ada pengiriman aktif yang ditugaskan ke Anda.</p>
            </x-card>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($activeShipments as $shipment)
                    <x-card class="relative overflow-hidden group border-l-4 {{ $shipment->status === 'problem' ? 'border-l-red-500' : 'border-l-amber-500' }}">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600">
                                        {{ $shipment->code }}
                                    </span>
                                    <x-status-pill :status="$shipment->status" />
                                </div>
                                <h4 class="font-bold text-slate-800 text-lg">{{ $shipment->project->name }}</h4>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold tracking-wider uppercase text-slate-400 block mb-1">Kendaraan</span>
                                <span class="inline-block px-2 py-1 bg-slate-900 text-white text-xs font-bold rounded">{{ $shipment->vehicle_plate }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 text-sm text-slate-600 mb-4 p-3 bg-slate-50 rounded-lg">
                            <div class="flex flex-col items-center gap-1">
                                <div class="w-2 h-2 rounded-full bg-slate-400"></div>
                                <div class="w-0.5 h-6 bg-slate-200"></div>
                                <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                            </div>
                            <div class="flex-1 space-y-3">
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Dari</p>
                                    <p class="font-medium text-slate-800">{{ $shipment->warehouse->name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Tujuan (Proyek)</p>
                                    <p class="font-medium text-slate-800">{{ $shipment->project->location ?? 'Lokasi Proyek' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-sm text-slate-500">
                                {{ $shipment->items->sum('quantity') }} item barang
                            </span>
                            <a href="{{ route('shipments.show', $shipment) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-600 hover:text-amber-700 transition">
                                Lihat Detail →
                            </a>
                        </div>
                    </x-card>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
