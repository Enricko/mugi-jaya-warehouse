@php
    $user = auth()->user();
    $role = $user?->role;

    // label, icon, route name, roles allowed
    $menu = [
        ['Dashboard',      'dashboard', 'dashboard',            ['owner', 'kepala_gudang', 'mandor', 'driver', 'engineering']],
        ['Notifikasi',     'bell',      'notifications.index',  ['owner', 'kepala_gudang', 'mandor', 'driver', 'engineering']],
        ['Gudang',         'warehouse', 'warehouses.index',     ['owner', 'kepala_gudang']],
        ['Inventaris',     'inventory', 'inventory.index',      ['owner', 'kepala_gudang']],
        ['Barang Masuk',   'inbound',   'inbound.index',        ['kepala_gudang', 'mandor']],
        ['Transfer',       'transfer',  'transfers.index',      ['kepala_gudang']],
        ['Proyek',         'project',   'projects.index',       ['owner', 'kepala_gudang']],
        ['Pengiriman',     'shipment',  'shipments.index',      ['owner', 'kepala_gudang']],
        ['GPS Tracking',   'gps',       'gps.index',            ['owner']],
        ['Supplier',       'supplier',  'suppliers.index',      ['owner', 'kepala_gudang']],
        ['Purchase Order', 'po',        'purchase-orders.index',['owner', 'kepala_gudang']],
        ['Laporan',        'report',    'reports.index',        ['owner', 'kepala_gudang']],
        ['Audit Log',      'audit',     'audit-log.index',      ['owner']],
    ];

    $roleLabels = [
        'owner' => 'Owner',
        'kepala_gudang' => 'Kepala Gudang',
        'mandor' => 'Mandor',
        'driver' => 'Driver',
        'engineering' => 'Engineering',
    ];
@endphp

<aside class="fixed inset-y-0 left-0 w-64 bg-slate-900 text-slate-300 flex flex-col z-30">
    {{-- Brand --}}
    <div class="flex items-center gap-3 px-5 h-16 shrink-0">
        <div class="w-9 h-9 rounded-lg bg-amber-500 text-slate-900 font-bold grid place-items-center text-sm">MJ</div>
        <div class="leading-tight">
            <p class="text-white font-semibold text-sm">Mugi Jaya</p>
            <p class="text-[10px] tracking-widest text-slate-500">WMS · V1.0</p>
        </div>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5">
        <p class="px-3 pt-2 pb-1 text-[10px] font-semibold tracking-widest text-slate-500 uppercase">Menu Utama</p>
        @foreach($menu as [$label, $icon, $routeName, $roles])
            @continue(! in_array($role, $roles))
            @php $exists = \Illuminate\Support\Facades\Route::has($routeName); @endphp
            @php $active = $exists && (request()->routeIs($routeName) || request()->routeIs(\Illuminate\Support\Str::before($routeName, '.').'.*')); @endphp
            <a href="{{ $exists ? route($routeName) : '#' }}"
               @class([
                   'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition',
                   'bg-amber-500/10 text-amber-400 font-semibold' => $active,
                   'hover:bg-slate-800 hover:text-white' => ! $active,
                   'opacity-40 pointer-events-none' => ! $exists,
               ])>
                <x-icon :name="$icon" class="w-[18px] h-[18px]" />
                <span>{{ $label }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Footer / user --}}
    <div class="border-t border-slate-800 p-3 shrink-0">
        @if(\Illuminate\Support\Facades\Route::has('settings.index'))
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm hover:bg-slate-800 hover:text-white transition mb-1">
                <x-icon name="settings" class="w-[18px] h-[18px]" />
                <span>Pengaturan</span>
            </a>
        @endif
        <div class="flex items-center gap-3 px-3 py-2">
            <div class="w-8 h-8 rounded-full bg-amber-500 text-slate-900 grid place-items-center text-xs font-bold">
                {{ strtoupper(\Illuminate\Support\Str::substr($user?->full_name ?? '?', 0, 2)) }}
            </div>
            <div class="leading-tight min-w-0">
                <p class="text-white text-sm font-medium truncate">{{ $user?->full_name }}</p>
                <p class="text-[11px] text-slate-500">{{ $roleLabels[$role] ?? $role }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="ml-auto">
                @csrf
                <button type="submit" title="Keluar" class="text-slate-500 hover:text-red-400 transition">
                    <x-icon name="logout" class="w-[18px] h-[18px]" />
                </button>
            </form>
        </div>
    </div>
</aside>
