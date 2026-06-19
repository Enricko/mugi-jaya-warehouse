@extends('layouts.app')

@section('title', 'Inventaris')
@section('breadcrumb', 'Gudang · Inventaris')
@section('page-title', 'Inventory')

@section('topbar-actions')
    <button onclick="document.getElementById('addMaterial').classList.remove('hidden')"
            class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-slate-800">
        <x-icon name="plus" class="w-4 h-4" /> Tambah Material
    </button>
@endsection

@php
    $rp = fn ($v) => 'Rp ' . number_format($v, 0, ',', '.');
    $qty = fn ($v) => rtrim(rtrim(number_format($v, 2), '0'), '.');
@endphp

@section('content')
<div class="space-y-4">
    {{-- KPI row --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        <x-stat-card label="Aluminium" :value="$qty($kpi['aluminium']) . ' m'" accent="slate" />
        <x-stat-card label="Kaca" :value="$qty($kpi['kaca']) . ' lbr'" accent="slate" />
        <x-stat-card label="Aksesori" :value="$qty($kpi['aksesori']) . ' pcs'" accent="amber" />
        <x-stat-card label="Stok Kritis" :value="$kpi['critical'] . ' item'" accent="red" />
        <x-stat-card label="Tanpa Tag Lokasi" :value="$kpi['untagged'] . ' item'" accent="amber" />
    </div>

    {{-- Materials saved to the catalogue but not yet stocked in any warehouse.
         These were previously invisible here (only the audit log showed them). --}}
    @if($stocklessMaterials->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-3">
                <x-icon name="alert" class="w-4 h-4 text-amber-600" />
                <h3 class="font-semibold text-amber-800 text-sm">Material Belum Punya Stok di Gudang ({{ $stocklessMaterials->count() }})</h3>
            </div>
            <p class="text-xs text-amber-700/80 mb-3">Material berikut sudah tersimpan tapi belum terhubung ke gudang manapun. Tambahkan ke gudang agar muncul di daftar inventaris.</p>
            <div class="space-y-2">
                @foreach($stocklessMaterials as $m)
                    <form method="POST" action="{{ route('inventory.stock') }}" class="flex flex-wrap items-center gap-2 bg-white rounded-lg border border-amber-100 px-3 py-2">
                        @csrf
                        <input type="hidden" name="material_id" value="{{ $m->id }}">
                        <div class="min-w-40 flex-1">
                            <span class="font-mono text-xs text-slate-400">{{ $m->sku }}</span>
                            <span class="font-medium text-slate-700 text-sm">{{ $m->name }}</span>
                            <span class="text-xs text-slate-400">· {{ $m->category }}</span>
                        </div>
                        <select name="warehouse_id" required class="text-sm rounded-lg border border-slate-200 px-2 py-1.5">
                            <option value="">Pilih gudang…</option>
                            @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                        </select>
                        <input name="quantity" type="number" step="0.01" min="0" value="0" required placeholder="Qty" class="w-24 text-sm rounded-lg border border-slate-200 px-2 py-1.5">
                        <input name="location_tag" placeholder="Rak (opsional)" class="w-28 text-sm rounded-lg border border-slate-200 px-2 py-1.5">
                        <button class="bg-amber-500 text-slate-900 text-sm font-semibold px-3 py-1.5 rounded-lg hover:bg-amber-400">Simpan ke Gudang</button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    <x-card>
        {{-- Filters --}}
        <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
            <div class="flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 flex-1 min-w-48">
                <x-icon name="search" class="w-4 h-4 text-slate-400" />
                <input name="search" value="{{ request('search') }}" placeholder="Cari SKU, nama material, lokasi rak…" class="bg-transparent text-sm outline-none flex-1">
            </div>
            <select name="warehouse" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Gudang: Semua</option>
                @foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(request('warehouse')==$w->id)>{{ $w->name }}</option>@endforeach
            </select>
            <select name="category" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Kategori: Semua</option>
                @foreach(['Aluminium','Kaca','Aksesori'] as $c)<option value="{{ $c }}" @selected(request('category')==$c)>{{ $c }}</option>@endforeach
            </select>
            <select name="project" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Proyek: Semua</option>
                <option value="umum" @selected(request('project')=='umum')>Umum (tanpa proyek)</option>
                @foreach($projects as $p)<option value="{{ $p->id }}" @selected(request('project')==$p->id)>{{ $p->name }}</option>@endforeach
            </select>
            <select name="status" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
                <option value="">Status: Semua</option>
                <option value="critical" @selected(request('status')=='critical')>Kritis</option>
                <option value="untagged" @selected(request('status')=='untagged')>Tanpa Tag</option>
            </select>
            <button class="bg-amber-500 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-amber-400">Filter</button>
        </form>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                        <th class="py-2">SKU</th><th>Nama Material</th><th>Kategori</th><th>Gudang</th><th>Lokasi Rak</th><th>Proyek</th><th class="text-right">Kuantitas</th><th class="text-right">Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stocks as $s)
                        <tr class="{{ $s->isLow() ? 'bg-red-50/40' : '' }}">
                            <td class="py-2.5 font-mono text-xs text-slate-500">{{ $s->material->sku }}</td>
                            <td class="font-medium text-slate-700">
                                @if($s->isLow())<x-icon name="alert" class="w-3.5 h-3.5 inline text-red-500" />@endif
                                {{ $s->material->name }}
                            </td>
                            <td class="text-slate-500">{{ $s->material->category }}</td>
                            <td class="text-slate-500">{{ \Illuminate\Support\Str::after($s->warehouse->name, '— ') }}</td>
                            <td>
                                @if($s->location_tag)
                                    {{ $s->location_tag }}
                                @else
                                    <form method="POST" action="{{ route('inventory.tag', $s) }}" class="flex items-center gap-1">
                                        @csrf
                                        <input name="location_tag" placeholder="belum tag" required class="w-24 text-xs italic text-amber-600 border-b border-amber-300 bg-transparent outline-none">
                                        <button class="text-amber-600"><x-icon name="pin" class="w-3.5 h-3.5" /></button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-slate-500">{{ $s->project?->name ? \Illuminate\Support\Str::limit($s->project->name, 16) : 'umum' }}</td>
                            <td class="text-right font-semibold {{ $s->isLow() ? 'text-red-600' : 'text-slate-700' }}">{{ $qty($s->quantity) }} {{ $s->material->unit }}</td>
                            <td class="text-right text-slate-600">{{ $rp($s->quantity * $s->material->purchase_price) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-slate-400">Tidak ada material yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $stocks->links() }}</div>
    </x-card>
</div>

{{-- Add material modal --}}
<div id="addMaterial" class="hidden fixed inset-0 z-50 bg-black/40 grid place-items-center p-4">
    <div class="bg-white rounded-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-800">Tambah Material</h3>
            <button onclick="document.getElementById('addMaterial').classList.add('hidden')" class="text-slate-400"><x-icon name="x" class="w-5 h-5" /></button>
        </div>
        <form method="POST" action="{{ route('inventory.store') }}" class="grid grid-cols-2 gap-3">
            @csrf
            <div><label class="text-xs text-slate-500">SKU</label><input name="sku" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            <div><label class="text-xs text-slate-500">Nama</label><input name="name" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            <div><label class="text-xs text-slate-500">Satuan</label><input name="unit" required placeholder="m / lembar / pcs" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            <div><label class="text-xs text-slate-500">Kategori</label>
                <select name="category" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"><option>Aluminium</option><option>Kaca</option><option>Aksesori</option></select>
            </div>
            <div><label class="text-xs text-slate-500">Harga Beli</label><input name="purchase_price" type="number" min="0" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            <div><label class="text-xs text-slate-500">Min Stok</label><input name="min_stock" type="number" min="0" value="0" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            <div><label class="text-xs text-slate-500">Gudang (opsional)</label>
                <select name="warehouse_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"><option value="">—</option>@foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select>
            </div>
            <div><label class="text-xs text-slate-500">Stok Awal (opsional)</label><input name="quantity" type="number" min="0" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            <div class="col-span-2 flex justify-end gap-2 mt-2">
                <button type="button" onclick="document.getElementById('addMaterial').classList.add('hidden')" class="px-4 py-2 text-sm rounded-lg bg-slate-100">Batal</button>
                <button class="px-4 py-2 text-sm rounded-lg bg-slate-900 text-white font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
