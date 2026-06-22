@extends('layouts.app')

@section('title', 'Buat PO')
@section('breadcrumb', 'Supplier · Purchase Order')
@section('page-title', 'Buat Purchase Order')

@section('content')
<form method="POST" action="{{ route('purchase-orders.store') }}" class="max-w-4xl space-y-4">
    @csrf
    @if($errors->any())<div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2">{{ $errors->first() }}</div>@endif

    <x-card title="Informasi PO">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="text-xs text-slate-500">Supplier</label>
                <select name="supplier_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="">Pilih supplier…</option>
                    @foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->city }})</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500">Gudang Tujuan</label>
                <select name="warehouse_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="">— Tentukan saat barang masuk —</option>
                    @foreach($warehouses as $w)<option value="{{ $w->id }}" @selected(old('warehouse_id')===$w->id)>{{ $w->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-slate-500">Tanggal Dibutuhkan</label>
                <input type="date" name="needed_date" value="{{ old('needed_date') }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
            </div>
        </div>
    </x-card>

    <x-card title="Item Pesanan">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                <th class="py-2">Material</th><th class="w-24">Qty</th><th class="w-36">Harga Satuan</th><th class="w-36 text-right">Subtotal</th><th></th>
            </tr></thead>
            <tbody id="itemsBody"></tbody>
            <tfoot><tr class="border-t border-slate-200"><td colspan="3" class="py-3 text-right font-semibold text-slate-600">Total Estimasi</td><td class="text-right font-bold text-slate-800" id="grandTotal">Rp 0</td><td></td></tr></tfoot>
        </table>
        <button type="button" onclick="addRow()" class="mt-3 inline-flex items-center gap-1 text-sm text-amber-600 font-medium"><x-icon name="plus" class="w-4 h-4" /> Tambah Item</button>
    </x-card>

    <div class="flex justify-end gap-2">
        <button name="submit" value="draft" class="px-4 py-2.5 text-sm rounded-lg bg-slate-100 text-slate-700 font-semibold">Simpan Draft</button>
        <button name="submit" value="pending" class="px-5 py-2.5 text-sm rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800">Submit untuk Approval</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const materials = @json($materialsJson);
    let idx = 0;
    const body = document.getElementById('itemsBody');
    const fmt = n => 'Rp ' + (n||0).toLocaleString('id-ID');

    function recalc() {
        let total = 0;
        body.querySelectorAll('tr').forEach(tr => {
            const q = parseFloat(tr.querySelector('.q').value) || 0;
            const p = parseFloat(tr.querySelector('.p').value) || 0;
            tr.querySelector('.sub').textContent = fmt(q*p);
            total += q*p;
        });
        document.getElementById('grandTotal').textContent = fmt(total);
    }
    function addRow() {
        const i = idx++;
        const opts = materials.map(m => `<option value="${m.id}" data-price="${m.price}">${m.sku} · ${m.name}</option>`).join('');
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-50';
        tr.innerHTML = `
            <td class="py-2 pr-2"><select name="items[${i}][material_id]" required class="w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm material">${opts}</select></td>
            <td class="pr-2"><input name="items[${i}][quantity]" type="number" step="0.01" min="0.01" value="1" required class="q w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm"></td>
            <td class="pr-2"><input name="items[${i}][unit_price]" type="number" step="1" min="0" required class="p w-full rounded-lg border border-slate-200 px-2 py-1.5 text-sm"></td>
            <td class="text-right text-slate-600 sub">Rp 0</td>
            <td><button type="button" class="text-slate-300 hover:text-red-500" onclick="this.closest('tr').remove();recalc()">✕</button></td>`;
        body.appendChild(tr);
        const sel = tr.querySelector('.material'), price = tr.querySelector('.p');
        const setPrice = () => { price.value = sel.selectedOptions[0].dataset.price; recalc(); };
        sel.addEventListener('change', setPrice);
        tr.querySelector('.q').addEventListener('input', recalc);
        price.addEventListener('input', recalc);
        setPrice();
    }
    addRow();
</script>
@endpush
