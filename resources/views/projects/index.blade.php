@extends('layouts.app')

@section('title', 'Proyek')
@section('breadcrumb', 'Proyek · Daftar')
@section('page-title', 'Daftar Proyek')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach($projects as $p)
        <a href="{{ route('projects.show', $p) }}" class="block bg-white rounded-xl border border-slate-200/70 shadow-sm p-5 hover:border-amber-300 transition">
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
    @endforeach
</div>
@endsection
