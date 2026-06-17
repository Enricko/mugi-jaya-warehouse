@extends('layouts.app')

@section('title', 'Request Transfer')
@section('breadcrumb', 'Gudang · Transfer')
@section('page-title', 'Request Transfer Antar Gudang')

@section('content')
<div class="max-w-2xl">
    <x-card>
        @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2">
                {{ $errors->first() }}
            </div>
        @endif
        <form method="POST" action="{{ route('transfers.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-slate-500">Gudang Asal</label>
                    <select name="from_warehouse_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Pilih…</option>
                        @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Gudang Tujuan</label>
                    <select name="to_warehouse_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Pilih…</option>
                        @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-slate-500">Material</label>
                    <select name="material_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Pilih material…</option>
                        @foreach($materials as $m)<option value="{{ $m->id }}">{{ $m->sku }} · {{ $m->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Kuantitas</label>
                    <input name="quantity" type="number" step="0.01" min="0.01" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="text-xs text-slate-500">Catatan</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea>
            </div>
            <div class="flex items-center justify-between pt-2">
                <p class="text-xs text-slate-400">Transfer akan menunggu approval Kepala Gudang / Owner sebelum stok dipindahkan.</p>
                <button class="bg-slate-900 text-white font-semibold rounded-lg px-5 py-2.5 text-sm hover:bg-slate-800">Kirim Request</button>
            </div>
        </form>
    </x-card>
</div>
@endsection
