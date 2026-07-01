@extends('layouts.app')

@section('title', 'Dashboard Mandor')
@section('breadcrumb', 'Beranda · Mandor')
@section('page-title', 'Selamat Datang, ' . $user->full_name)

@section('content')
<div class="space-y-6">
    @if(! $warehouse)
        <x-card class="py-8 text-center border-dashed border-red-200 bg-red-50">
            <x-icon name="alert" class="w-12 h-12 text-red-400 mx-auto mb-3" />
            <h3 class="text-lg font-bold text-red-800 mb-1">Gudang Belum Ditetapkan</h3>
            <p class="text-red-600 text-sm">Anda belum ditugaskan untuk mengelola gudang manapun. Silakan hubungi Kepala Gudang.</p>
        </x-card>
    @else
        {{-- Welcome & Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-card class="md:col-span-2 flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-500 text-slate-900 font-bold grid place-items-center text-lg shrink-0">
                    {{ strtoupper(\Illuminate\Support\Str::substr($user->full_name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Selamat bertugas, {{ $user->full_name }}!</h2>
                    <p class="text-sm text-slate-500">Mengelola gudang: <strong class="text-slate-700">{{ $warehouse->name }}</strong></p>
                </div>
            </x-card>

            <x-card class="flex items-center gap-4 border-l-4 border-l-red-500">
                <div class="w-12 h-12 rounded-xl bg-red-500/10 text-red-600 grid place-items-center shrink-0">
                    <x-icon name="alert" class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Stok Kritis</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $criticalCount }} <span class="text-sm font-normal text-slate-500">item</span></p>
                </div>
            </x-card>
            
            <x-card class="flex items-center gap-4 border-l-4 border-l-amber-500">
                <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-600 grid place-items-center shrink-0">
                    <x-icon name="inbound" class="w-6 h-6" />
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500">Masuk (Pending)</p>
                    <p class="text-2xl font-bold text-slate-800">{{ $inboundPending->count() }} <span class="text-sm font-normal text-slate-500">antrean</span></p>
                </div>
            </x-card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mt-2">
            {{-- Pending Inbound --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-base font-bold text-slate-800">
                        Butuh Proses Masuk
                    </h3>
                    @if($inboundPending->isNotEmpty())
                        <a href="{{ route('inbound.index') }}" class="text-sm text-amber-600 hover:text-amber-700 font-medium">Lihat Semua</a>
                    @endif
                </div>
                
                @if($inboundPending->isEmpty())
                    <x-card class="py-10 text-center border-dashed">
                        <p class="text-sm text-slate-500">Tidak ada antrean barang masuk.</p>
                    </x-card>
                @else
                    <div class="space-y-3">
                        @foreach($inboundPending as $inbound)
                            <x-card class="p-4 hover:shadow-md transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-amber-100 text-amber-800 mb-1 inline-block">
                                            {{ $inbound->code }}
                                        </span>
                                        <h4 class="font-bold text-slate-800">{{ $inbound->material->name }}</h4>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-bold text-slate-700">{{ $inbound->quantity }}</span>
                                        <span class="text-xs text-slate-500">{{ $inbound->material->unit }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between mt-4 text-xs text-slate-500 border-t border-slate-100 pt-3">
                                    <span class="flex items-center gap-1"><x-icon name="user" class="w-3.5 h-3.5"/> {{ $inbound->creator->full_name }}</span>
                                    <span>{{ $inbound->created_at->diffForHumans() }}</span>
                                </div>
                            </x-card>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Inbound History --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-base font-bold text-slate-800">
                        Riwayat Terakhir
                    </h3>
                </div>
                
                <x-card class="p-0 overflow-hidden">
                    @if($inboundRecent->isEmpty())
                        <div class="py-10 text-center">
                            <p class="text-sm text-slate-500">Belum ada riwayat barang masuk.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100">
                            @foreach($inboundRecent as $recent)
                                <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-green-50 text-green-600 grid place-items-center shrink-0">
                                            <x-icon name="inbound" class="w-5 h-5" />
                                        </div>
                                        <div>
                                            <p class="font-semibold text-slate-800 text-sm">{{ $recent->material->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $recent->code }} · {{ $recent->created_at->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-green-600 text-sm">+{{ $recent->quantity }} {{ $recent->material->unit }}</p>
                                        <x-status-pill :status="$recent->status" />
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </x-card>
            </div>
        </div>
    @endif
</div>
@endsection
