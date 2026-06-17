@extends('layouts.app')

@section('title', 'Catat Barang Masuk')
@section('breadcrumb', 'Gudang · Barang Masuk')
@section('page-title', 'Catat Barang Masuk')

@section('content')
<form method="POST" action="{{ route('inbound.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    @csrf
    <div class="lg:col-span-2 space-y-4">
        <x-card title="1 · Sumber Penerimaan">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-slate-500">Purchase Order (opsional)</label>
                    <select name="purchase_order_id" id="poSelect" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">— Tanpa PO (manual) —</option>
                        @foreach($pos as $po)
                            <option value="{{ $po->id }}">{{ $po->po_number }} · {{ $po->supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Gudang Tujuan</label>
                    <select name="warehouse_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Pilih gudang…</option>
                        @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs text-slate-500">Asal Barang / No. Surat Jalan</label>
                    <input name="source" placeholder="PT Sumber Alumindo · SJ/SA/2026/0521" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
            </div>
        </x-card>

        <x-card title="2 · Konfirmasi Kuantitas" subtitle="Isi kuantitas aktual yang diterima — selisih terhadap PO dicatat otomatis">
            <table class="w-full text-sm" id="itemsTable">
                <thead>
                    <tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                        <th class="py-2">Material</th><th class="w-28">Qty PO</th><th class="w-28">Qty Aktual</th><th class="w-32">Lokasi Rak</th><th></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    {{-- rows injected by JS --}}
                </tbody>
            </table>
            <button type="button" onclick="addRow()" class="mt-3 inline-flex items-center gap-1 text-sm text-amber-600 font-medium">
                <x-icon name="plus" class="w-4 h-4" /> Tambah Item
            </button>
        </x-card>
    </div>

    <div class="space-y-4">
        <x-card title="3 · Bukti Foto Kondisi" subtitle="Foto kondisi barang (opsional, maks 5MB/foto)">
            <input type="file" name="photos[]" multiple accept="image/*" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-900 file:text-white file:text-xs">
            <p class="text-[11px] text-slate-400 mt-2">Pada perangkat mobile, foto otomatis menyertakan GPS tag &amp; timestamp.</p>
        </x-card>

        <x-card title="Catatan & Ringkasan">
            <textarea name="notes" rows="3" placeholder="Keterangan tambahan / keterangan selisih…" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea>
            <button class="mt-3 w-full bg-slate-900 text-white font-semibold rounded-lg py-2.5 text-sm hover:bg-slate-800 inline-flex items-center justify-center gap-1.5">
                <x-icon name="check" class="w-4 h-4" /> Submit & Tambah Stok
            </button>
        </x-card>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const materials = @json($materialsJson);
    const poItems = @json($poItems);
    let idx = 0;
    const body = document.getElementById('itemsBody');

    function optionList(selectedId) {
        return materials.map(m => `<option value="${m.id}" ${m.id===selectedId?'selected':''}>${m.sku} · ${m.name}</option>`).join('');
    }
    function addRow(data = {}) {
        const i = idx++;
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-50';
        tr.innerHTML = `
            <td class="py-2 pr-2"><select name="items[${i}][material_id]" required class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm">${optionList(data.material_id)}</select></td>
            <td class="pr-2"><input name="items[${i}][qty_po]" type="number" step="0.01" value="${data.qty ?? ''}" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm bg-slate-50"></td>
            <td class="pr-2"><input name="items[${i}][qty_actual]" type="number" step="0.01" value="${data.qty ?? ''}" required class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm"></td>
            <td class="pr-2"><input name="items[${i}][location_tag]" placeholder="Rak…" class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm"></td>
            <td><button type="button" class="text-slate-300 hover:text-red-500" onclick="this.closest('tr').remove()">✕</button></td>`;
        body.appendChild(tr);
    }

    document.getElementById('poSelect').addEventListener('change', function () {
        body.innerHTML = ''; idx = 0;
        const items = poItems[this.value];
        if (items && items.length) {
            items.forEach(it => addRow(it));
        } else {
            addRow();
        }
    });

    addRow();
</script>
@endpush
