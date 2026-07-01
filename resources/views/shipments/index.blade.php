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
        <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
            <div class="flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 flex-1 min-w-48">
                <x-icon name="search" class="w-4 h-4 text-slate-400" />
                <input name="search" value="{{ request('search') }}" placeholder="Cari kode pengiriman, plat kendaraan…" class="bg-transparent text-sm outline-none flex-1">
            </div>
            <select name="project" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Proyek: Semua</option>
                @foreach($projects as $p)<option value="{{ $p->id }}" @selected(request('project')==$p->id)>{{ $p->name }}</option>@endforeach
            </select>
            <select name="warehouse" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Gudang: Semua</option>
                @foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('warehouse')==$w->id)>{{ $w->name }}</option>@endforeach
            </select>
            <select name="driver" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Driver: Semua</option>
                @foreach($drivers as $d)<option value="{{ $d->id }}" @selected(request('driver')==$d->id)>{{ $d->full_name }}</option>@endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm rounded-lg border border-slate-200 px-3 py-2" title="Dari tanggal">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm rounded-lg border border-slate-200 px-3 py-2" title="Sampai tanggal">
            @if($statusFilter)<input type="hidden" name="status" value="{{ $statusFilter }}">@endif
            <button class="bg-amber-500 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-amber-400">Filter</button>
            @if(request()->hasAny(['search','project','warehouse','driver','date_from','date_to']))
                <a href="{{ route('shipments.index', $statusFilter ? ['status' => $statusFilter] : []) }}" class="text-xs text-slate-400 hover:text-red-500">✕ Reset</a>
            @endif
        </form>

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
