@extends('layouts.app')

@section('title', 'Pengaturan')
@section('breadcrumb', 'Pengaturan')
@section('page-title', 'Pengaturan')

@php
    $roleLabels = ['owner'=>'Owner','kepala_gudang'=>'Kepala Gudang','mandor'=>'Mandor','driver'=>'Driver','engineering'=>'Engineering'];
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="space-y-4">
        <x-card title="Profil">
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-400">Nama</dt><dd class="text-slate-700 font-medium">{{ $user->full_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Email</dt><dd class="text-slate-700">{{ $user->email }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Telepon</dt><dd class="text-slate-700">{{ $user->phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">Role</dt><dd class="text-slate-700">{{ $roleLabels[$user->role] ?? $user->role }}</dd></div>
            </dl>
        </x-card>

        <x-card title="Ubah Password">
            @if($errors->any())<div class="mb-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('settings.password') }}" class="space-y-3">
                @csrf @method('PUT')
                <div><label class="text-xs text-slate-500">Password Saat Ini</label><input type="password" name="current_password" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                <div><label class="text-xs text-slate-500">Password Baru</label><input type="password" name="password" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                <div><label class="text-xs text-slate-500">Konfirmasi Password Baru</label><input type="password" name="password_confirmation" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                <button class="w-full bg-slate-900 text-white text-sm font-semibold rounded-lg py-2 hover:bg-slate-800">Simpan Password</button>
            </form>
        </x-card>
    </div>

    @if($canManage)
        <div class="lg:col-span-2 space-y-4">
            <x-card title="Tambah Akun Baru" subtitle="Akun didaftarkan oleh atasan (akses berjenjang)">
                <form method="POST" action="{{ route('users.store') }}" class="grid grid-cols-2 gap-3">
                    @csrf
                    <div><label class="text-xs text-slate-500">Nama Lengkap</label><input name="full_name" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Email</label><input type="email" name="email" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Telepon</label><input name="phone" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="text-xs text-slate-500">Role</label>
                        <select name="role" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            @foreach($allowedRoles as $r)<option value="{{ $r }}">{{ $roleLabels[$r] ?? $r }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-span-2"><label class="text-xs text-slate-500">Password Awal</label><input type="password" name="password" required minlength="8" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div class="col-span-2 flex justify-end"><button class="bg-slate-900 text-white text-sm font-semibold rounded-lg px-5 py-2 hover:bg-slate-800">Buat Akun</button></div>
                </form>
            </x-card>

            <x-card title="Daftar Pengguna" :subtitle="$users->count() . ' akun'">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead><tr class="text-left text-[11px] uppercase tracking-wide text-slate-400 border-b border-slate-100">
                            <th class="py-2">Nama</th><th>Email</th><th>Role</th><th>Didaftarkan Oleh</th><th>Status</th><th></th>
                        </tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($users as $u)
                                <tr>
                                    <td class="py-2.5 font-medium text-slate-700">{{ $u->full_name }}</td>
                                    <td class="text-slate-500">{{ $u->email }}</td>
                                    <td class="text-slate-500">{{ $roleLabels[$u->role] ?? $u->role }}</td>
                                    <td class="text-slate-400 text-xs">{{ $u->creator?->full_name ?? '—' }}</td>
                                    <td>@if($u->is_active)<span class="text-green-600 text-xs font-medium">● Aktif</span>@else<span class="text-slate-400 text-xs">○ Nonaktif</span>@endif</td>
                                    <td class="text-right">
                                        @if($u->id !== $user->id && $u->role !== 'owner')
                                            <form method="POST" action="{{ route('users.toggle', $u) }}">@csrf
                                                <button class="text-xs text-amber-600 font-medium">{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>
    @endif
</div>
@endsection
