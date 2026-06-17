@extends('layouts.app')

@section('title', 'Dashboard Operasional')
@section('breadcrumb', 'Beranda · Kepala Gudang')
@section('page-title', 'Dashboard Operasional')

@php
    $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
    $url = fn ($n, $p = []) => \Illuminate\Support\Facades\Route::has($n) ? route($n, $p) : '#';
@endphp

@section('content')
<div class="space-y-6">
    {{-- Critical stock banner + quick actions --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
        <div class="bg-red-50 border border-red-200 rounded-xl p-5 flex items-center gap-5">
            <div class="text-center">
                <div class="w-12 h-12 rounded-xl bg-red-500 text-white grid place-items-center mx-auto">
                    <x-icon name="alert" class="w-6 h-6" />
                </div>
                <p class="text-3xl font-bold text-red-600 mt-2">{{ $criticalCount }}</p>
                <p class="text-[11px] tracking-widest text-red-500 font-semibold">STOK KRITIS</p>
            </div>
            <div class="flex-1 grid grid-cols-2 gap-x-4 gap-y-2">
                @foreach($critical as $s)
                    <div>
                        <p class="text-xs font-semibold text-slate-700">{{ $s->material->sku }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $s->material->name }}</p>
                        <p class="text-xs"><span class="font-bold text-red-600">{{ $qty($s->quantity) }} {{ $s->material->unit }}</span>
                            <span class="text-slate-400">/ min {{ $s->material->min_stock }} · {{ \Illuminate\Support\Str::after($s->warehouse->name, '— ') }}</span></p>
                    </div>
                @endforeach
            </div>
        </div>

        <x-card class="xl:col-span-2">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 h-full items-center">
                <a href="{{ $url('purchase-orders.create') }}" class="flex flex-col gap-2 p-4 rounded-lg border border-slate-100 hover:border-amber-300 hover:bg-amber-50 transition">
                    <span class="w-9 h-9 rounded-lg bg-slate-900 text-white grid place-items-center"><x-icon name="plus" class="w-5 h-5" /></span>
                    <span class="text-sm font-semibold text-slate-700">Buat PO</span>
                    <span class="text-[11px] text-slate-400">Aksi cepat</span>
                </a>
                <a href="{{ $url('shipments.create') }}" class="flex flex-col gap-2 p-4 rounded-lg border border-slate-100 hover:border-amber-300 hover:bg-amber-50 transition">
                    <span class="w-9 h-9 rounded-lg bg-blue-600 text-white grid place-items-center"><x-icon name="truck" class="w-5 h-5" /></span>
                    <span class="text-sm font-semibold text-slate-700">Tugaskan Driver</span>
                    <span class="text-[11px] text-slate-400">Aksi cepat</span>
                </a>
                <a href="{{ $url('inventory.index') }}" class="flex flex-col gap-2 p-4 rounded-lg border border-slate-100 hover:border-amber-300 hover:bg-amber-50 transition">
                    <span class="w-9 h-9 rounded-lg bg-green-600 text-white grid place-items-center"><x-icon name="inventory" class="w-5 h-5" /></span>
                    <span class="text-sm font-semibold text-slate-700">Cek Stok</span>
                    <span class="text-[11px] text-slate-400">Aksi cepat</span>
                </a>
                <a href="{{ $url('reports.index') }}" class="flex flex-col gap-2 p-4 rounded-lg border border-slate-100 hover:border-amber-300 hover:bg-amber-50 transition">
                    <span class="w-9 h-9 rounded-lg bg-amber-500 text-slate-900 grid place-items-center"><x-icon name="report" class="w-5 h-5" /></span>
                    <span class="text-sm font-semibold text-slate-700">Generate Laporan</span>
                    <span class="text-[11px] text-slate-400">Aksi cepat</span>
                </a>
            </div>
        </x-card>
    </div>

    {{-- Transfer approvals + mandor activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <x-card class="lg:col-span-2">
            <div class="flex items-center gap-2 mb-3">
                <h3 class="font-semibold text-slate-800">Transfer Antar Gudang</h3>
                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[11px] font-semibold">{{ $pendingTransfers->count() }} MENUNGGU ANDA</span>
            </div>
            <div class="space-y-3">
                @forelse($pendingTransfers as $t)
                    <div class="p-4 rounded-lg border border-slate-100">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-700">{{ $t->code }}</span>
                                <x-status-pill status="pending" />
                            </div>
                            <span class="text-sm font-bold text-slate-800">{{ $rp($t->amount) }}</span>
                        </div>
                        <p class="text-sm font-medium text-slate-700">{{ $t->material?->name }} · {{ $qty($t->quantity) }} {{ $t->material?->unit }}</p>
                        <p class="text-xs text-slate-400 mb-3">
                            {{ \Illuminate\Support\Str::after($t->fromWarehouse?->name, '— ') }} → <span class="font-medium text-slate-600">{{ \Illuminate\Support\Str::after($t->toWarehouse?->name, '— ') }}</span>
                            · oleh {{ $t->creator->full_name }} · {{ $t->created_at->diffForHumans() }}
                        </p>
                        @if(\Illuminate\Support\Facades\Route::has('transfers.approve'))
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('transfers.approve', $t) }}">
                                @csrf
                                <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700">
                                    <x-icon name="check" class="w-4 h-4" /> Setuju
                                </button>
                            </form>
                            <button type="button"
                                    onclick="document.getElementById('reject-{{ $t->id }}').classList.toggle('hidden')"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-red-100 hover:text-red-600">
                                <x-icon name="x" class="w-4 h-4" /> Tolak
                            </button>
                        </div>
                        <form id="reject-{{ $t->id }}" method="POST" action="{{ route('transfers.reject', $t) }}" class="hidden mt-2">
                            @csrf
                            <textarea name="reason" required minlength="10" rows="2" placeholder="Alasan penolakan (min 10 karakter)…"
                                      class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-red-400 outline-none"></textarea>
                            <button class="mt-1 px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-semibold">Kirim Penolakan</button>
                        </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-6">Tidak ada transfer menunggu approval.</p>
                @endforelse
            </div>
        </x-card>

        <x-card title="Aktivitas Mandor Hari Ini" :subtitle="$mandors->count() . ' mandor · ' . $mandors->count() . ' gudang'">
            <div class="space-y-4">
                @foreach($mandors as $i => $mandor)
                    @php $pct = [85, 60, 40, 25][$i % 4]; @endphp
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-8 h-8 rounded-full bg-slate-800 text-white grid place-items-center text-[11px] font-bold">
                                {{ strtoupper(\Illuminate\Support\Str::substr(\Illuminate\Support\Str::after($mandor->full_name, 'Mandor '), 0, 2)) }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-slate-700 truncate">{{ $mandor->full_name }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $mandor->managedWarehouse?->name ?? 'Belum ditugaskan' }}</p>
                            </div>
                            <span class="text-xs font-semibold text-slate-500">{{ $pct }}%</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full bg-amber-500" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    {{-- Material tanpa tag lokasi --}}
    <x-card>
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="font-semibold text-slate-800">Material Tanpa Tag Lokasi</h3>
                <p class="text-xs text-slate-400">Material masuk yang belum ditandai lokasi rak — minta mandor tindak lanjut</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">{{ $untagged->count() }} ITEM</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
            @forelse($untagged as $s)
                <div class="p-3 rounded-lg border border-slate-100">
                    <p class="text-xs font-semibold text-slate-700">{{ $s->material->sku }}</p>
                    <p class="text-sm text-slate-700 truncate">{{ $s->material->name }} · {{ $qty($s->quantity) }} {{ $s->material->unit }}</p>
                    <div class="flex items-center justify-between mt-2">
                        <span class="text-[11px] text-slate-400">{{ \Illuminate\Support\Str::after($s->warehouse->name, '— ') }}</span>
                        <a href="{{ $url('inventory.index') }}" class="text-[11px] text-amber-600 font-medium inline-flex items-center gap-1"><x-icon name="pin" class="w-3 h-3" /> Tag</a>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-400 py-2">Semua material sudah memiliki tag lokasi.</p>
            @endforelse
        </div>
    </x-card>
</div>
@endsection
