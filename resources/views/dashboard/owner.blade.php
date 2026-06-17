@extends('layouts.app')

@section('title', 'Dashboard Owner')
@section('breadcrumb', 'Beranda · Owner View')
@section('page-title', 'Selamat datang, ' . $user->full_name)

@php
    $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $rpShort = function ($v) {
        if ($v >= 1_000_000_000) return 'Rp ' . number_format($v / 1_000_000_000, 2, ',', '.') . ' M';
        if ($v >= 1_000_000) return 'Rp ' . number_format($v / 1_000_000, 1, ',', '.') . ' Jt';
        return 'Rp ' . number_format($v, 0, ',', '.');
    };
    $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
    $url = fn ($n, $p = []) => \Illuminate\Support\Facades\Route::has($n) ? route($n, $p) : '#';
@endphp

@section('content')
<div class="space-y-6">
    {{-- KPI row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <x-stat-card label="Nilai Inventory" :value="$rpShort($kpi['inventory_value'])" accent="slate" trend="3.2% vs minggu lalu" :trendUp="true" />
        <x-stat-card label="Proyek Aktif" :value="$kpi['active_projects']" accent="slate" trend="+2 vs minggu lalu" :trendUp="true" />
        <x-stat-card label="Pengiriman Hari Ini" :value="$kpi['shipments_today']" accent="blue">
            <p class="mt-1 text-xs text-blue-600">{{ $kpi['in_transit'] }} in transit</p>
        </x-stat-card>
        <x-stat-card label="Alert Kritis" :value="$kpi['critical']" accent="red">
            <p class="mt-1 text-xs text-red-500">butuh tindak lanjut</p>
        </x-stat-card>
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-card title="Konsumsi Material" subtitle="14 hari terakhir (nilai konsumsi)">
            <div class="h-56"><canvas id="consumptionChart"></canvas></div>
        </x-card>
        <x-card title="Pengiriman" subtitle="14 hari terakhir">
            <div class="h-56"><canvas id="shipmentChart"></canvas></div>
        </x-card>
    </div>

    {{-- Approval + GPS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <x-card>
            <x-slot:actions>
                <a href="{{ $url('transfers.index') }}" class="text-xs text-amber-600 font-medium">Lihat semua →</a>
            </x-slot:actions>
            <div class="flex items-center gap-2 mb-3">
                <h3 class="font-semibold text-slate-800">Approval Pending</h3>
                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[11px] font-semibold">
                    {{ $pendingTransfers->count() + $pendingPos->count() }} MENUNGGU
                </span>
            </div>
            <div class="space-y-2">
                @foreach($pendingPos as $po)
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:bg-slate-50">
                        <span class="w-9 h-9 rounded-lg bg-slate-900 text-white grid place-items-center text-[11px] font-bold">PO</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $po->po_number }} · {{ $po->supplier->name }}</p>
                            <p class="text-xs text-slate-400">oleh {{ $po->creator->full_name }}</p>
                        </div>
                        <p class="text-sm font-semibold text-slate-800">{{ $rp($po->total_estimated) }}</p>
                        <a href="{{ $url('purchase-orders.show', $po) }}" class="text-xs text-amber-600 font-medium">Detail</a>
                    </div>
                @endforeach
                @forelse($pendingTransfers as $t)
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:bg-slate-50">
                        <span class="w-9 h-9 rounded-lg bg-amber-500 text-slate-900 grid place-items-center text-[11px] font-bold">TRF</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-800 truncate">{{ $t->fromWarehouse?->name }} → {{ $t->toWarehouse?->name }}</p>
                            <p class="text-xs text-slate-400">{{ $t->material?->name }} · {{ $qty($t->quantity) }} · oleh {{ $t->creator->full_name }}</p>
                        </div>
                        <p class="text-sm font-semibold text-slate-800">{{ $rp($t->amount) }}</p>
                        <a href="{{ $url('transfers.index') }}" class="text-xs text-amber-600 font-medium">Detail</a>
                    </div>
                @empty
                    @if($pendingPos->isEmpty())
                        <p class="text-sm text-slate-400 text-center py-6">Tidak ada approval tertunda.</p>
                    @endif
                @endforelse
            </div>
        </x-card>

        <x-card>
            <x-slot:actions>
                <a href="{{ $url('gps.index') }}" class="text-xs text-amber-600 font-medium">Full map →</a>
            </x-slot:actions>
            <div class="flex items-center gap-2 mb-3">
                <h3 class="font-semibold text-slate-800">Live GPS Tracking</h3>
                <span class="text-xs text-slate-400">{{ $activeShipments->count() }} kendaraan aktif</span>
            </div>
            <div id="miniMap" class="h-72 rounded-lg overflow-hidden border border-slate-200 z-0"></div>
        </x-card>
    </div>

    {{-- Recent activity --}}
    <x-card title="Aktivitas Terbaru">
        <div class="divide-y divide-slate-100">
            @forelse($recentActivity as $t)
                <div class="flex items-center gap-3 py-3">
                    <span class="text-xs text-slate-400 w-12">{{ $t->created_at->format('H:i') }}</span>
                    <x-status-pill :status="$t->status" />
                    <p class="text-sm text-slate-700 flex-1 truncate">
                        <span class="font-medium">{{ $t->creator->full_name }}</span>
                        — {{ $t->code }} · {{ ucfirst($t->type) }} {{ $t->material?->name }}
                    </p>
                    <span class="text-sm font-medium text-slate-500">{{ $rp($t->amount) }}</span>
                </div>
            @empty
                <p class="text-sm text-slate-400 py-4 text-center">Belum ada aktivitas.</p>
            @endforelse
        </div>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
    const consumption = @json($consumptionChart);
    const shipment = @json($shipmentChart);
    const activeShipments = @json($activeShipmentsJson);

    new Chart(document.getElementById('consumptionChart'), {
        type: 'line',
        data: { labels: consumption.labels, datasets: [{ label: 'Konsumsi (Rp)', data: consumption.data, borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.12)', fill: true, tension: .35, pointRadius: 0 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => 'Rp ' + (v/1e6) + 'Jt' } } } }
    });

    new Chart(document.getElementById('shipmentChart'), {
        type: 'bar',
        data: { labels: shipment.labels, datasets: [
            { label: 'Terkirim', data: shipment.delivered, backgroundColor: '#22c55e' },
            { label: 'Bermasalah', data: shipment.problem, backgroundColor: '#ef4444' },
        ] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { x: { stacked: true }, y: { stacked: true, ticks: { precision: 0 } } } }
    });

    const map = L.map('miniMap').setView([-6.25, 106.95], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 18 }).addTo(map);
    activeShipments.forEach(s => {
        if (!s.lat || !s.lng) return;
        L.marker([s.lat, s.lng]).addTo(map).bindPopup(`<b>${s.plate}</b><br>${s.driver}<br>${s.project}`);
    });
</script>
@endpush
