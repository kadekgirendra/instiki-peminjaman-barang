<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sistem Peminjaman Barang Kampus INSTIKI' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background min-h-screen">
    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="w-64 bg-secondary text-white flex flex-col shrink-0">
            <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
                <img src="{{ asset('images/logo-instiki.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                <div>
                    <p class="font-bold leading-tight">INSTIKI</p>
                    <p class="text-xs text-slate-400 leading-tight">Sistem Peminjaman</p>
                </div>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-1">
                @php
                    $navItems = [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
                        ['route' => 'items.index', 'label' => 'Katalog', 'icon' => 'box'],
                        ['route' => 'transactions.index', 'label' => 'Pinjaman Saya', 'icon' => 'box-outline'],
                        ['route' => 'transactions.history', 'label' => 'History', 'icon' => 'clock'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @php $isActive = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition
                              {{ $isActive ? 'bg-primary text-white' : 'text-slate-300 hover:bg-white/5' }}">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col">

            {{-- Navbar atas --}}
            <header class="bg-white px-8 py-4 flex items-center justify-end gap-4 border-b border-slate-100">
                <button class="relative text-slate-500">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-primary rounded-full"></span>
                </button>

                <div class="text-right leading-tight">
                    <p class="font-semibold text-secondary text-sm">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400">{{ auth()->user()->isAdmin() ? 'Admin' : 'Mahasiswa' }}</p>
                </div>

                <div class="w-9 h-9 rounded-full bg-secondary text-white flex items-center justify-center font-semibold text-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </header>

            {{-- Konten halaman --}}
            <main class="flex-1 p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>