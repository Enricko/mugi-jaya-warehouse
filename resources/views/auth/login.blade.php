<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Mugi Jaya WMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full">
<div class="min-h-full grid lg:grid-cols-2">
    {{-- Left: brand panel --}}
    <div class="hidden lg:flex flex-col justify-between bg-slate-950 text-white p-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-900 via-slate-950 to-black"></div>
        <div class="relative">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-lg bg-amber-500 text-slate-900 font-bold grid place-items-center">MJ</div>
                <div class="leading-tight">
                    <p class="font-semibold">CV. Mugi Jaya</p>
                    <p class="text-[10px] tracking-widest text-slate-400">ALUMINIUM · KACA · BEKASI</p>
                </div>
            </div>
        </div>

        <div class="relative max-w-md">
            <p class="text-amber-500 text-xs tracking-widest mb-4">// WAREHOUSE MANAGEMENT SYSTEM</p>
            <h2 class="text-4xl font-bold leading-tight">
                Visibilitas penuh dari <span class="text-amber-500">gudang</span> ke lokasi proyek.
            </h2>
            <p class="text-slate-400 mt-4 text-sm leading-relaxed">
                Pelacakan stok real-time, bukti foto delivery dengan GPS tag, dan approval workflow
                berjenjang — menggantikan buku catatan fisik dengan akuntabilitas digital.
            </p>
            <div class="flex gap-10 mt-10">
                <div><p class="text-3xl font-bold text-amber-500">8</p><p class="text-[10px] tracking-widest text-slate-500 mt-1">MODUL TERINTEGRASI</p></div>
                <div><p class="text-3xl font-bold text-amber-500">31</p><p class="text-[10px] tracking-widest text-slate-500 mt-1">USE CASE</p></div>
                <div><p class="text-3xl font-bold text-amber-500">99%</p><p class="text-[10px] tracking-widest text-slate-500 mt-1">UPTIME SLA</p></div>
            </div>
        </div>

        <div class="relative flex justify-between text-[10px] tracking-widest text-slate-600">
            <span>© 2025 CV. MUGI JAYA</span>
            <span>BUILT BY FURRY TEAM</span>
        </div>
    </div>

    {{-- Right: form --}}
    <div class="flex items-center justify-center bg-slate-50 p-8">
        <div class="w-full max-w-sm">
            <p class="text-[11px] tracking-widest text-slate-400 mb-1">MASUK KE SISTEM</p>
            <h1 class="text-2xl font-bold text-slate-900">Selamat datang kembali.</h1>
            <p class="text-sm text-slate-500 mt-1 mb-6">Masukkan kredensial untuk mengakses dashboard.</p>

            <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
                           placeholder="owner@mugijaya.com">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-sm font-medium text-slate-700">Password</label>
                        <span class="text-xs text-slate-400">Lupa password?</span>
                    </div>
                    <input type="password" name="password" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"
                           placeholder="••••••••••">
                </div>

                @error('email')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    Ingat saya di perangkat ini
                </label>

                <button type="submit"
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-lg py-2.5 text-sm transition">
                    Masuk &rarr;
                </button>

                <div class="rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs px-3 py-2.5 leading-relaxed">
                    <strong>Akses berjenjang.</strong> Akun didaftarkan oleh atasan langsung.
                    Hubungi Pak Yudi atau Pak Sukma untuk request akun baru.
                </div>
            </form>

            <div class="flex justify-between text-[10px] tracking-widest text-slate-400 mt-8">
                <span>v1.0.0 · BUILD 2026.06</span>
                <span>STATUS: <span class="text-green-500">● OPERATIONAL</span></span>
            </div>
        </div>
    </div>
</div>
</body>
</html>
