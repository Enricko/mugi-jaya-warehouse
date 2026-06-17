@extends('layouts.app')

@section('title', 'Supplier')
@section('breadcrumb', 'Supplier · Direktori')
@section('page-title', 'Direktori Supplier')

@section('topbar-actions')
    <button onclick="document.getElementById('addSupplier').classList.remove('hidden')" class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-slate-800">
        <x-icon name="plus" class="w-4 h-4" /> Tambah Supplier
    </button>
@endsection

@section('content')
<x-card>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                <th class="py-2">Nama</th><th>Kota</th><th>Kontak</th><th>Tipe</th><th>PO</th><th>Status</th><th></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($suppliers as $s)
                    <tr>
                        <td class="py-2.5 font-medium text-slate-700">{{ $s->name }}</td>
                        <td class="text-slate-500">{{ $s->city ?? '—' }}</td>
                        <td class="text-slate-500">{{ $s->contact_phone ?? '—' }}</td>
                        <td>
                            @if($s->is_external_island)
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[11px] font-semibold">Luar Pulau</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 text-[11px] font-semibold">Dalam Pulau</span>
                            @endif
                        </td>
                        <td class="text-slate-600">{{ $s->purchase_orders_count }}</td>
                        <td>
                            @if($s->is_active)<span class="text-green-600 text-xs font-medium">● Aktif</span>
                            @else<span class="text-slate-400 text-xs font-medium">○ Nonaktif</span>@endif
                        </td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('suppliers.toggle', $s) }}">@csrf
                                <button class="text-xs text-amber-600 font-medium">{{ $s->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada supplier.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

<div id="addSupplier" class="hidden fixed inset-0 z-50 bg-black/40 grid place-items-center p-4">
    <div class="bg-white rounded-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-800">Tambah Supplier</h3>
            <button onclick="document.getElementById('addSupplier').classList.add('hidden')" class="text-slate-400"><x-icon name="x" class="w-5 h-5" /></button>
        </div>
        <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-3">
            @csrf
            <div><label class="text-xs text-slate-500">Nama Supplier</label><input name="name" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-xs text-slate-500">Kota</label><input name="city" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                <div><label class="text-xs text-slate-500">Kontak</label><input name="contact_phone" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            </div>
            <div><label class="text-xs text-slate-500">Alamat</label><textarea name="address" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea></div>
            <label class="flex items-center gap-2 text-sm text-slate-600"><input type="checkbox" name="is_external_island" value="1" class="rounded border-slate-300 text-amber-500"> Supplier luar pulau (lead time khusus)</label>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('addSupplier').classList.add('hidden')" class="px-4 py-2 text-sm rounded-lg bg-slate-100">Batal</button>
                <button class="px-4 py-2 text-sm rounded-lg bg-slate-900 text-white font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
