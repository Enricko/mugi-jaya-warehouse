@extends('layouts.app')

@section('title', 'Pengiriman')
@section('breadcrumb', 'Pengiriman · Daftar')
@section('page-title', 'Pengiriman')

@section('topbar-actions')
    @if(\Illuminate\Support\Facades\Route::has('shipments.create'))
        <a href="{{ route('shipments.create') }}" class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-slate-800">
            <x-icon name="plus" class="w-4 h-4" /> Tugaskan Driver
        </a>
    @endif
@endsection

@php $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.'); @endphp

@section('content')
<div class="space-y-4">
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('shipments.index') }}" @class(['px-3 py-1.5 rounded-lg text-sm font-medium', 'bg-slate-900 text-white' => ! $statusFilter, 'bg-white border border-slate-200 text-slate-600' => $statusFilter])>Semua</a>
        @foreach(['confirmed'=>'Confirmed','in_transit'=>'In Transit','delivered'=>'Delivered','problem'=>'Problem'] as $k=>$label)
            <a href="{{ route('shipments.index', ['status'=>$k]) }}" @class(['px-3 py-1.5 rounded-lg text-sm font-medium', 'bg-slate-900 text-white' => $statusFilter===$k, 'bg-white border border-slate-200 text-slate-600' => $statusFilter!==$k])>
                {{ $label }} <span class="opacity-60">{{ $counts[$k] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                    <th class="py-2">Kode</th><th>Tujuan</th><th>Driver</th><th>Kendaraan</th><th>Gudang Asal</th><th>Status</th><th>Tanggal</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($shipments as $sh)
                        <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('shipments.show', $sh) }}'">
                            <td class="py-2.5 font-mono text-xs text-slate-600">{{ $sh->code }}</td>
                            <td class="font-medium text-slate-700">{{ $sh->project->name }}</td>
                            <td class="text-slate-600">{{ $sh->driver->full_name }}</td>
                            <td class="text-slate-500">{{ $sh->vehicle_plate }}</td>
                            <td class="text-slate-500">{{ \Illuminate\Support\Str::after($sh->warehouse->name, '— ') }}</td>
                            <td><x-status-pill :status="$sh->status" /></td>
                            <td class="text-slate-400 text-xs">{{ $sh->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">Tidak ada pengiriman.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $shipments->links() }}</div>
    </x-card>
</div>
@endsection
