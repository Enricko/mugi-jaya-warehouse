@extends('layouts.app')

@section('title', 'Gudang')
@section('breadcrumb', 'Gudang · Daftar')
@section('page-title', 'Daftar Gudang')

@php $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.'); @endphp

@section('topbar-actions')
    <button onclick="document.getElementById('addWarehouse').classList.remove('hidden')" class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-slate-800">
        <x-icon name="plus" class="w-4 h-4" /> Tambah Gudang
    </button>
@endsection

@section('content')
@if($errors->any())<div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2">{{ $errors->first() }}</div>@endif
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-4">
    @forelse($warehouses as $w)
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
    @empty
        <div class="col-span-full py-12 text-center text-slate-400">Belum ada gudang. Klik “Tambah Gudang” untuk membuat.</div>
    @endforelse
</div>

<div id="addWarehouse" class="hidden fixed inset-0 z-50 bg-black/40 grid place-items-center p-4">
    <div class="bg-white rounded-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-800">Tambah Gudang</h3>
            <button onclick="document.getElementById('addWarehouse').classList.add('hidden')" class="text-slate-400"><x-icon name="x" class="w-5 h-5" /></button>
        </div>
        <form method="POST" action="{{ route('warehouses.store') }}" class="space-y-3">
            @csrf
            <div><label class="text-xs text-slate-500">Nama Gudang</label><input name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            <div><label class="text-xs text-slate-500">Alamat</label><textarea name="address" rows="2" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">{{ old('address') }}</textarea></div>
            <div>
                <label class="text-xs text-slate-500">Mandor Penanggung Jawab (opsional)</label>
                <select name="mandor_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="">— Belum ditentukan —</option>
                    @foreach($mandors as $m)<option value="{{ $m->id }}" @selected(old('mandor_id')===$m->id)>{{ $m->full_name }}</option>@endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-xs text-slate-500">Latitude (opsional)</label><input name="latitude" value="{{ old('latitude') }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="-6.2088"></div>
                <div><label class="text-xs text-slate-500">Longitude (opsional)</label><input name="longitude" value="{{ old('longitude') }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="106.8456"></div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('addWarehouse').classList.add('hidden')" class="px-4 py-2 text-sm rounded-lg bg-slate-100">Batal</button>
                <button class="px-4 py-2 text-sm rounded-lg bg-slate-900 text-white font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@if($errors->any())<script>document.getElementById('addWarehouse').classList.remove('hidden')</script>@endif
@endpush
