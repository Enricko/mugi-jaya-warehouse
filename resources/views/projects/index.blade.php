@extends('layouts.app')

@section('title', 'Proyek')
@section('breadcrumb', 'Proyek · Daftar')
@section('page-title', 'Daftar Proyek')

@section('topbar-actions')
    <button onclick="document.getElementById('addProject').classList.remove('hidden')" class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-slate-800">
        <x-icon name="plus" class="w-4 h-4" /> Tambah Proyek
    </button>
@endsection

@section('content')
@if($errors->any())<div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2">{{ $errors->first() }}</div>@endif
<div class="space-y-4">
    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" class="flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 flex-1 min-w-48">
            <x-icon name="search" class="w-4 h-4 text-slate-400" />
            <input name="search" value="{{ request('search') }}" placeholder="Cari nama proyek, klien, lokasi…" class="bg-transparent text-sm outline-none flex-1">
            @if($statusFilter)<input type="hidden" name="status" value="{{ $statusFilter }}">@endif
        </form>
        <a href="{{ route('projects.index', request('search') ? ['search' => request('search')] : []) }}" @class(['px-3 py-1.5 rounded-lg text-sm font-medium', 'bg-slate-900 text-white' => ! $statusFilter, 'bg-white border border-slate-200 text-slate-600' => $statusFilter])>Semua</a>
        @foreach(['planning'=>'Planning','active'=>'Active','on_hold'=>'On Hold','completed'=>'Completed'] as $k=>$label)
            <a href="{{ route('projects.index', array_filter(['status'=>$k, 'search'=>request('search')])) }}" @class(['px-3 py-1.5 rounded-lg text-sm font-medium', 'bg-slate-900 text-white' => $statusFilter===$k, 'bg-white border border-slate-200 text-slate-600' => $statusFilter!==$k])>{{ $label }}</a>
        @endforeach
    </div>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($projects as $p)
        <a href="{{ route('projects.show', $p) }}" class="block bg-white rounded-xl border border-slate-200/70 shadow-sm p-5 hover:border-amber-300 hover:shadow-md transition">
            <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white grid place-items-center"><x-icon name="project" class="w-5 h-5" /></div>
                <x-status-pill :status="$p->status" />
            </div>
            <h3 class="font-semibold text-slate-800 mt-3">{{ $p->name }}</h3>
            <p class="text-xs text-slate-400">{{ $p->client_name }}</p>
            <p class="text-xs text-slate-500 mt-2">{{ $p->location }}</p>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100 text-xs">
                <span class="text-slate-400">{{ $p->shipments_count }} pengiriman</span>
                <span class="text-slate-400">{{ $p->start_date?->format('d M Y') ?? '—' }}</span>
            </div>
        </a>
    @empty
        <div class="col-span-full py-12 text-center text-slate-400">Belum ada proyek. Klik “Tambah Proyek” untuk membuat.</div>
    @endforelse
</div>
</div>

<div id="addProject" class="hidden fixed inset-0 z-50 bg-black/40 grid place-items-center p-4">
    <div class="bg-white rounded-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-800">Tambah Proyek</h3>
            <button onclick="document.getElementById('addProject').classList.add('hidden')" class="text-slate-400"><x-icon name="x" class="w-5 h-5" /></button>
        </div>
        <form method="POST" action="{{ route('projects.store') }}" class="space-y-3">
            @csrf
            <div><label class="text-xs text-slate-500">Nama Proyek</label><input name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-xs text-slate-500">Klien</label><input name="client_name" value="{{ old('client_name') }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                <div>
                    <label class="text-xs text-slate-500">Status</label>
                    <select name="status" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="planning" @selected(old('status')==='planning')>Planning</option>
                        <option value="active" @selected(old('status', 'active')==='active')>Active</option>
                        <option value="on_hold" @selected(old('status')==='on_hold')>On Hold</option>
                        <option value="completed" @selected(old('status')==='completed')>Completed</option>
                    </select>
                </div>
            </div>
            <div><label class="text-xs text-slate-500">Lokasi</label><input name="location" value="{{ old('location') }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="text-xs text-slate-500">Tanggal Mulai</label><input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                <div><label class="text-xs text-slate-500">Tanggal Selesai</label><input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('addProject').classList.add('hidden')" class="px-4 py-2 text-sm rounded-lg bg-slate-100">Batal</button>
                <button class="px-4 py-2 text-sm rounded-lg bg-slate-900 text-white font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
@if($errors->any())<script>document.getElementById('addProject').classList.remove('hidden')</script>@endif
@endpush
