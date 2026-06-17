@extends('layouts.app')

@section('title', 'Tugaskan Driver')
@section('breadcrumb', 'Pengiriman · Baru')
@section('page-title', 'Tugaskan Driver & Kendaraan')

@section('content')
<form method="POST" action="{{ route('shipments.store') }}" class="max-w-3xl space-y-4">
    @csrf
    @if($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2">{{ $errors->first() }}</div>
    @endif

    <x-card title="Detail Pengiriman">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="text-xs text-slate-500">Proyek Tujuan</label>
                <select name="project_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="">Pilih proyek…</option>
                    @foreach($projects as $p)<option value="{{ $p->id }}">{{ $p->name }} — {{ $p->client_name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500">Gudang Asal</label>
                <select name="warehouse_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="">Pilih gudang…</option>
                    @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500">Driver</label>
                <select name="driver_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="">Pilih driver…</option>
                    @foreach($drivers as $d)<option value="{{ $d->id }}">{{ $d->full_name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500">Plat Kendaraan</label>
                <input name="vehicle_plate" required placeholder="B 9143 TKO" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
            </div>
        </div>
    </x-card>

    <x-card title="Muatan">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100"><th class="py-2">Material</th><th class="w-32">Kuantitas</th><th></th></tr></thead>
            <tbody id="itemsBody"></tbody>
        </table>
        <button type="button" onclick="addRow()" class="mt-3 inline-flex items-center gap-1 text-sm text-amber-600 font-medium"><x-icon name="plus" class="w-4 h-4" /> Tambah Item</button>
    </x-card>

    <div class="flex justify-end">
        <button class="bg-slate-900 text-white font-semibold rounded-lg px-5 py-2.5 text-sm hover:bg-slate-800">Buat Pengiriman</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const materials = @json($materialsJson);
    let idx = 0;
    const body = document.getElementById('itemsBody');
    function addRow() {
        const i = idx++;
        const opts = materials.map(m => `<option value="${m.id}">${m.sku} · ${m.name}</option>`).join('');
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-50';
        tr.innerHTML = `
            <td class="py-2 pr-2"><select name="items[${i}][material_id]" required class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm">${opts}</select></td>
            <td class="pr-2"><input name="items[${i}][quantity]" type="number" step="0.01" min="0.01" required class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm"></td>
            <td><button type="button" class="text-slate-300 hover:text-red-500" onclick="this.closest('tr').remove()">✕</button></td>`;
        body.appendChild(tr);
    }
    addRow();
</script>
@endpush
