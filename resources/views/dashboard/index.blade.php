@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Beranda')
@section('page-title', 'Dashboard')

@section('content')
    <x-card>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500 text-slate-900 font-bold grid place-items-center text-lg">
                {{ strtoupper(\Illuminate\Support\Str::substr($user->full_name, 0, 2)) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Selamat datang, {{ $user->full_name }}.</h2>
                <p class="text-sm text-slate-500">Anda masuk sebagai <span class="font-medium capitalize">{{ str_replace('_', ' ', $user->role) }}</span>. Modul dashboard lengkap sedang dipasang.</p>
            </div>
        </div>
    </x-card>
@endsection
