@extends('layouts.app')

@section('title', 'Notifikasi')
@section('breadcrumb', 'Akun · Notifikasi')
@section('page-title', 'Notifikasi')

@section('topbar-actions')
    @if($unreadCount > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="inline-flex items-center gap-1.5 bg-slate-900 text-white text-sm font-semibold px-3 py-2 rounded-lg hover:bg-slate-800">
                <x-icon name="check" class="w-4 h-4" /> Tandai semua dibaca
            </button>
        </form>
    @endif
@endsection

@php
    $styles = [
        'alert'   => ['bg' => 'bg-red-100',   'text' => 'text-red-600',   'icon' => 'alert'],
        'warning' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'icon' => 'alert'],
        'info'    => ['bg' => 'bg-slate-100', 'text' => 'text-slate-500', 'icon' => 'bell'],
    ];
@endphp

@section('content')
<div class="space-y-4">
    {{-- Filter tabs --}}
    <div class="flex items-center gap-2">
        <a href="{{ route('notifications.index') }}"
           @class(['px-3 py-1.5 rounded-lg text-sm font-medium', 'bg-slate-900 text-white' => $filter !== 'unread', 'bg-white text-slate-600 border border-slate-200' => $filter === 'unread'])>
            Semua
        </a>
        <a href="{{ route('notifications.index', ['filter' => 'unread']) }}"
           @class(['px-3 py-1.5 rounded-lg text-sm font-medium inline-flex items-center gap-1.5', 'bg-slate-900 text-white' => $filter === 'unread', 'bg-white text-slate-600 border border-slate-200' => $filter !== 'unread'])>
            Belum dibaca
            @if($unreadCount > 0)<span class="bg-red-500 text-white text-[10px] px-1.5 rounded-full">{{ $unreadCount }}</span>@endif
        </a>
    </div>

    <x-card>
        <div class="divide-y divide-slate-100">
            @forelse($notifications as $n)
                @php $s = $styles[$n->type] ?? $styles['info']; @endphp
                <div class="flex items-start gap-3 py-3 {{ $n->is_read ? '' : 'bg-amber-50/40 -mx-5 px-5' }}">
                    <div class="w-9 h-9 rounded-lg {{ $s['bg'] }} {{ $s['text'] }} grid place-items-center shrink-0">
                        <x-icon :name="$s['icon']" class="w-4 h-4" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-slate-800 text-sm">{{ $n->title }}</p>
                            @unless($n->is_read)<span class="w-2 h-2 rounded-full bg-red-500 shrink-0"></span>@endunless
                        </div>
                        @if($n->message)<p class="text-sm text-slate-500 mt-0.5">{{ $n->message }}</p>@endif
                        <div class="flex items-center gap-2 mt-1.5">
                            @if($n->module)<span class="text-[10px] uppercase tracking-wide text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded">{{ $n->module }}</span>@endif
                            <span class="text-xs text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    @unless($n->is_read)
                        <form method="POST" action="{{ route('notifications.read', $n) }}" class="shrink-0">
                            @csrf
                            <button class="text-xs text-amber-600 font-medium hover:text-amber-700 inline-flex items-center gap-1">
                                <x-icon name="check" class="w-3.5 h-3.5" /> Tandai dibaca
                            </button>
                        </form>
                    @endunless
                </div>
            @empty
                <div class="py-12 text-center text-slate-400">
                    <x-icon name="bell" class="w-8 h-8 mx-auto mb-2 text-slate-300" />
                    {{ $filter === 'unread' ? 'Tidak ada notifikasi yang belum dibaca.' : 'Belum ada notifikasi.' }}
                </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
            <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
    </x-card>
</div>
@endsection
