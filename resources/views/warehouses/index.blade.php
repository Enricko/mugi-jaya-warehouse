@extends('layouts.app')

@section('title', 'Gudang')
@section('breadcrumb', 'Gudang · Daftar')
@section('page-title', 'Daftar Gudang')

@php $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-4">
    @foreach($warehouses as $w)
        <a href="{{ route('warehouses.show', $w) }}" class="block bg-white rounded-xl border border-slate-200/70 shadow-sm p-5 hover:border-amber-300 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-slate-900 text-white grid place-items-center"><x-icon name="warehouse" class="w-6 h-6" /></div>
                    <div>
                        <h3 class="font-semibold text-slate-800">{{ $w->name }}</h3>
                        <p class="text-xs text-slate-400">{{ $w->address }}</p>
                    </div>
                </div>
                @if($w->critical_count > 0)
                    <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[11px] font-semibold">{{ $w->critical_count }} kritis</span>
                @else
                    <span class="px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[11px] font-semibold">sehat</span>
                @endif
            </div>
            <div class="grid grid-cols-3 gap-3 mt-4 pt-4 border-t border-slate-100">
                <div><p class="text-[11px] text-slate-400 uppercase tracking-wide">Item</p><p class="font-bold text-slate-800">{{ $w->item_count }}</p></div>
                <div><p class="text-[11px] text-slate-400 uppercase tracking-wide">Nilai Stok</p><p class="font-bold text-slate-800 text-sm">{{ $rp($w->stock_value) }}</p></div>
                <div><p class="text-[11px] text-slate-400 uppercase tracking-wide">Mandor</p><p class="font-medium text-slate-700 text-sm truncate">{{ $w->mandor?->full_name ?? '—' }}</p></div>
            </div>
        </a>
    @endforeach
</div>
@endsection
