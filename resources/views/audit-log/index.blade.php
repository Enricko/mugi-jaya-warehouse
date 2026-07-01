@extends('layouts.app')

@section('title', 'Audit Log')
@section('breadcrumb', 'Security · Audit Log')
@section('page-title', 'Audit Log')

@php
    $badge = [
        'CREATE' => 'bg-green-100 text-green-700',
        'UPDATE' => 'bg-blue-100 text-blue-700',
        'DELETE' => 'bg-red-100 text-red-700',
        'APPROVE' => 'bg-green-100 text-green-700',
        'REJECT' => 'bg-red-100 text-red-700',
    ];
@endphp

@section('content')
<x-card>
    <form method="GET" class="flex flex-wrap items-center gap-2 mb-4">
        <select name="action" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
            <option value="">Aksi: Semua</option>
            @foreach($actions as $a)<option value="{{ $a }}" @selected($actionFilter==$a)>{{ $a }}</option>@endforeach
        </select>
        <select name="module" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
            <option value="">Modul: Semua</option>
            @foreach($modules as $m)<option value="{{ $m }}" @selected($moduleFilter==$m)>{{ $m }}</option>@endforeach
        </select>
        <select name="user" class="text-sm rounded-lg border border-slate-200 px-3 py-2">
            <option value="">User: Semua</option>
            @foreach($users as $u)<option value="{{ $u->id }}" @selected(request('user')==$u->id)>{{ $u->full_name }}</option>@endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="text-sm rounded-lg border border-slate-200 px-3 py-2" title="Dari tanggal">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="text-sm rounded-lg border border-slate-200 px-3 py-2" title="Sampai tanggal">
        <button class="bg-amber-500 text-slate-900 text-sm font-semibold px-4 py-2 rounded-lg hover:bg-amber-400">Filter</button>
        @if(request()->hasAny(['action','module','user','date_from','date_to']))
            <a href="{{ route('audit-log.index') }}" class="text-xs text-slate-400 hover:text-red-500">✕ Reset</a>
        @endif
        <span class="text-xs text-slate-400 ml-auto">Audit log bersifat immutable — tidak dapat dihapus oleh user.</span>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                <th class="py-2">Waktu</th><th>User</th><th>Aksi</th><th>Modul</th><th>Entitas</th><th>IP</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                    <tr>
                        <td class="py-2.5 text-slate-500 text-xs">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                        <td class="text-slate-700">{{ $log->user?->full_name ?? 'Sistem' }}</td>
                        <td><span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $badge[$log->action] ?? 'bg-slate-100 text-slate-600' }}">{{ $log->action }}</span></td>
                        <td class="text-slate-500">{{ $log->module }}</td>
                        <td class="text-slate-500 text-xs font-mono">{{ class_basename($log->entity_type) }}</td>
                        <td class="text-slate-400 text-xs">{{ $log->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada catatan audit.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
</x-card>
@endsection
