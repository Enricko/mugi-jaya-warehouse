@extends('layouts.app')

@section('title', 'GPS Tracking')
@section('breadcrumb', 'Pengiriman · GPS Live')
@section('page-title', 'GPS Tracking')

@section('topbar-actions')
    <form method="POST" action="{{ route('gps.ping') }}">@csrf
        <button class="inline-flex items-center gap-1.5 bg-amber-500 text-slate-900 text-sm font-semibold px-3 py-2 rounded-lg hover:bg-amber-400">
            <span class="w-2 h-2 rounded-full bg-green-600 animate-pulse"></span> Live · Refresh Posisi
        </button>
    </form>
@endsection

@php
    $statusColors = ['in_transit'=>'text-blue-600','confirmed'=>'text-amber-600','delivered'=>'text-green-600','problem'=>'text-red-600'];
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Side list --}}
    <div class="space-y-3">
        <x-card>
            <h3 class="font-semibold text-slate-800 mb-1">Pengiriman Aktif</h3>
            <p class="text-xs text-slate-400 mb-3">{{ $markers->count() }} kendaraan dengan posisi GPS</p>
            <div class="space-y-2 max-h-[28rem] overflow-y-auto">
                @forelse($shipments as $sh)
                    <div class="p-3 rounded-lg border border-slate-100 hover:bg-slate-50">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs text-slate-500">{{ $sh->code }}</span>
                            <x-status-pill :status="$sh->status" />
                        </div>
                        <p class="text-sm font-semibold text-slate-700 mt-1">{{ $sh->vehicle_plate }}</p>
                        <p class="text-xs text-slate-500">{{ $sh->driver->full_name }} · {{ $sh->project->name }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Tidak ada pengiriman aktif.</p>
                @endforelse
            </div>
        </x-card>
        <x-card>
            <h4 class="text-xs font-semibold text-slate-500 uppercase mb-2">Status Kendaraan</h4>
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-blue-500"></span> In Transit <span class="ml-auto font-semibold">{{ $counts['in_transit'] ?? 0 }}</span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-green-500"></span> Delivered <span class="ml-auto font-semibold">{{ $counts['delivered'] ?? 0 }}</span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Confirmed <span class="ml-auto font-semibold">{{ $counts['confirmed'] ?? 0 }}</span></div>
                <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-red-500"></span> Problem <span class="ml-auto font-semibold">{{ $counts['problem'] ?? 0 }}</span></div>
            </div>
        </x-card>
    </div>

    {{-- Map --}}
    <div class="lg:col-span-2">
        <x-card>
            <div id="gpsMap" style="height:34rem;min-height:34rem" class="rounded-lg overflow-hidden border border-slate-200 z-0"></div>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const markers = @json($markers);
    const colors = { in_transit: '#3b82f6', confirmed: '#f59e0b', delivered: '#22c55e', problem: '#ef4444' };

    const map = L.map('gpsMap').setView([-6.27, 106.99], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 18 }).addTo(map);

    const bounds = [];
    markers.forEach(m => {
        const icon = L.divIcon({
            className: '',
            html: `<div style="background:${colors[m.status]||'#64748b'};color:#fff;padding:2px 6px;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap;box-shadow:0 1px 4px rgba(0,0,0,.3)">${m.plate}</div>`,
        });
        L.marker([m.lat, m.lng], { icon }).addTo(map)
            .bindPopup(`<b>${m.code} · ${m.plate}</b><br>${m.driver}<br>${m.project}<br><span style="text-transform:capitalize">${m.status.replace('_',' ')}</span>`);
        bounds.push([m.lat, m.lng]);
    });
    if (bounds.length) map.fitBounds(bounds, { padding: [40, 40], maxZoom: 13 });

    // Ensure tiles render correctly once layout settles
    setTimeout(() => map.invalidateSize(), 150);
    window.addEventListener('resize', () => map.invalidateSize());
</script>
@endpush
