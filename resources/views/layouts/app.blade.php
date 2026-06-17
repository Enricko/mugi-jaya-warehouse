<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Mugi Jaya WMS</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">
    <div class="min-h-full">
        @include('partials.sidebar')

        <div class="pl-64 flex flex-col min-h-screen">
            {{-- Topbar --}}
            <header class="sticky top-0 z-20 bg-white/90 backdrop-blur border-b border-slate-200 h-16 flex items-center gap-4 px-6">
                <div class="min-w-0">
                    @hasSection('breadcrumb')
                        <p class="text-[11px] tracking-widest text-slate-400 uppercase">@yield('breadcrumb')</p>
                    @endif
                    <h1 class="text-lg font-bold text-slate-800 leading-tight truncate">@yield('page-title', 'Dashboard')</h1>
                </div>

                <div class="ml-auto flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-2 bg-slate-100 rounded-lg px-3 py-2 w-64 focus-within:ring-2 focus-within:ring-amber-500 focus-within:bg-white transition">
                        <x-icon name="search" class="w-4 h-4 text-slate-400" />
                        <input type="text" placeholder="Cari material, proyek, driver…"
                               class="bg-transparent text-sm outline-none flex-1 placeholder:text-slate-400">
                    </div>

                    @yield('topbar-actions')

                    <button class="relative w-10 h-10 grid place-items-center rounded-lg hover:bg-slate-100 transition text-slate-500">
                        <x-icon name="bell" class="w-5 h-5" />
                        @php $unread = auth()->id() ? \App\Models\Notification::where('user_id', auth()->id())->where('is_read', false)->count() : 0; @endphp
                        @if($unread > 0)
                            <span class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-red-500 text-white text-[9px] grid place-items-center">{{ $unread > 9 ? '9+' : $unread }}</span>
                        @endif
                    </button>
                </div>
            </header>

            {{-- Flash messages --}}
            @if(session('success') || session('error'))
                <div class="px-6 pt-4">
                    @if(session('success'))
                        <div class="rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 flex items-center gap-2">
                            <x-icon name="check" class="w-4 h-4" /> {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 flex items-center gap-2">
                            <x-icon name="alert" class="w-4 h-4" /> {{ session('error') }}
                        </div>
                    @endif
                </div>
            @endif

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @stack('scripts')
</body>
</html>
