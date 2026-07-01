@extends('layouts.app')

@section('title', 'Transfer Antar Gudang')
@section('breadcrumb', 'Gudang · Transfer')
@section('page-title', 'Transfer Antar Gudang')

@section('topbar-actions')
    @if(\Illuminate\Support\Facades\Route::has('transfers.create'))
        <a href="{{ route('transfers.create') }}" class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-slate-800">
            <x-icon name="plus" class="w-4 h-4" /> Request Transfer
        </a>
    @endif
@endsection

@php
    $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
    $canApprove = in_array(auth()->user()->role, ['owner', 'kepala_gudang']);
@endphp

@section('content')
<div class="space-y-4">
    <x-card>
        <div class="flex items-center gap-2 mb-3">
            <h3 class="font-semibold text-slate-800">Menunggu Approval</h3>
            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[11px] font-semibold">{{ $pending->count() }}</span>
        </div>
        <div class="space-y-3">
            @forelse($pending as $t)
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
                    @if($canApprove)
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('transfers.approve', $t) }}">@csrf
                                <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700"><x-icon name="check" class="w-4 h-4" /> Setuju</button>
                            </form>
                            <button type="button" onclick="document.getElementById('rej-{{ $t->id }}').classList.toggle('hidden')" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-red-100 hover:text-red-600"><x-icon name="x" class="w-4 h-4" /> Tolak</button>
                        </div>
                        <form id="rej-{{ $t->id }}" method="POST" action="{{ route('transfers.reject', $t) }}" class="hidden mt-2">@csrf
                            <textarea name="reason" required minlength="10" rows="2" placeholder="Alasan penolakan (min 10 karakter)…" class="w-full text-xs rounded-lg border border-slate-200 px-3 py-2 outline-none focus:ring-2 focus:ring-red-400"></textarea>
                            <button class="mt-1 px-3 py-1.5 rounded-lg bg-red-600 text-white text-xs font-semibold">Kirim Penolakan</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-400 text-center py-6">Tidak ada transfer menunggu approval.</p>
            @endforelse
        </div>
    </x-card>

    <x-card title="Riwayat Transfer">
        <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
            <div class="flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 flex-1 min-w-48">
                <x-icon name="search" class="w-4 h-4 text-slate-400" />
                <input name="search" value="{{ request('search') }}" placeholder="Cari kode transfer…" class="bg-transparent text-sm outline-none flex-1">
            </div>
            <select name="status" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Status: Semua</option>
                @foreach(['approved'=>'Approved','rejected'=>'Rejected','completed'=>'Completed'] as $k=>$label)
                    <option value="{{ $k }}" @selected(request('status')==$k)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="from_warehouse" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Dari Gudang: Semua</option>
                @foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('from_warehouse')==$w->id)>{{ $w->name }}</option>@endforeach
            </select>
            <select name="to_warehouse" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Ke Gudang: Semua</option>
                @foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('to_warehouse')==$w->id)>{{ $w->name }}</option>@endforeach
            </select>
            <button class="bg-amber-500 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-amber-400">Filter</button>
            @if(request()->hasAny(['search','status','from_warehouse','to_warehouse']))
                <a href="{{ route('transfers.index') }}" class="text-xs text-slate-400 hover:text-red-500">✕ Reset</a>
            @endif
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                    <th class="py-2">Kode</th><th>Material</th><th>Rute</th><th>Status</th><th>Oleh</th><th class="text-right">Nilai</th><th>Tanggal</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($history as $t)
                        <tr>
                            <td class="py-2.5 font-mono text-xs text-slate-600">{{ $t->code }}</td>
                            <td class="text-slate-700">{{ $t->material?->name }} · {{ $qty($t->quantity) }}</td>
                            <td class="text-slate-500 text-xs">{{ \Illuminate\Support\Str::after($t->fromWarehouse?->name, '— ') }} → {{ \Illuminate\Support\Str::after($t->toWarehouse?->name, '— ') }}</td>
                            <td><x-status-pill :status="$t->status" /></td>
                            <td class="text-slate-500">{{ $t->creator->full_name }}</td>
                            <td class="text-right text-slate-600">{{ $rp($t->amount) }}</td>
                            <td class="text-slate-400 text-xs">{{ $t->created_at->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 text-center text-slate-400">Belum ada riwayat transfer.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $history->links() }}</div>
    </x-card>
</div>
@endsection
