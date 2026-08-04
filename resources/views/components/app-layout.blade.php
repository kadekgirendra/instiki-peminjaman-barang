<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sistem Peminjaman Barang Kampus INSTIKI' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-background">
    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar — CUMA muncul di desktop --}}
        <aside class="hidden lg:flex lg:flex-col w-64 bg-secondary text-white shrink-0 h-screen overflow-y-auto">
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
                        ['route' => 'transactions.index', 'label' => 'Pinjaman Saya', 'icon' => 'box'],
                        ['route' => 'transactions.history', 'label' => 'History', 'icon' => 'clock'],
                    ];
                @endphp

                @foreach ($navItems as $item)
                    @php $isActive = request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition
                                              {{ $isActive ? 'bg-primary text-white' : 'text-slate-300 hover:bg-white/5' }}">

                        @if ($item['icon'] === 'grid')
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                            </svg>
                        @elseif ($item['icon'] === 'box')
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8l-9-5-9 5 9 5 9-5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8v8l9 5 9-5V8" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 13v8" />
                            </svg>
                        @elseif ($item['icon'] === 'clock')
                            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 9a9 9 0 111.5 8.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4v5h5" />
                            </svg>
                        @endif

                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        {{-- Main area --}}
        <div class="flex-1 flex flex-col h-screen overflow-hidden min-w-0">

            {{-- Navbar --}}
            <header
                class="bg-white px-4 sm:px-8 py-4 flex items-center justify-between gap-4 border-b border-slate-100 shrink-0">

                {{-- Logo kecil — cuma muncul di mobile, gantiin posisi hamburger --}}
                <div class="flex items-center gap-2 lg:hidden">
                    <img src="{{ asset('images/logo-instiki.png') }}" alt="Logo" class="w-7 h-7 object-contain">
                    <span class="font-bold text-secondary text-sm">SiPinjam INSTIKI</span>
                </div>

                <div class="hidden lg:block"></div>

                <div class="flex items-center gap-3 sm:gap-4">
                    {{-- Dropdown Notifikasi --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="relative text-slate-500" aria-label="Notifikasi">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if ($reminders->count() > 0)
                                <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-primary rounded-full"></span>
                            @endif
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                            class="fixed sm:absolute left-3 right-3 sm:left-auto sm:right-0 top-16 sm:top-auto sm:mt-3 w-auto sm:w-96 max-h-[70vh] sm:max-h-none bg-white rounded-2xl shadow-xl overflow-hidden z-50 flex flex-col">

                            <div class="bg-secondary px-4 py-3 sm:px-6 sm:py-5 shrink-0">
                                <h3 class="text-white font-bold text-base sm:text-lg">Pengingat Pengembalian</h3>
                                <p class="text-slate-300 text-xs sm:text-sm mt-0.5">{{ $reminders->count() }} barang
                                    perlu perhatian</p>
                            </div>

                            <div class="overflow-y-auto flex-1">
                                @forelse ($reminders as $reminder)
                                    @php
                                        $today = now()->startOfDay();
                                        $endDate = $reminder->end_date->copy()->startOfDay();
                                        $daysDiff = $today->diffInDays($endDate, false);
                                        $isOverdue = $daysDiff < 0;
                                    @endphp

                                    <div
                                        class="flex items-start gap-3 px-6 py-4 border-l-4 {{ $isOverdue ? 'border-danger' : 'border-warning' }} border-b border-slate-100 last:border-b-0">
                                        <div
                                            class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0 {{ $isOverdue ? 'bg-danger' : 'bg-warning' }}">
                                            @if ($isOverdue)
                                                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                                </svg>
                                            @else
                                                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2">
                                                    <circle cx="12" cy="12" r="9" />
                                                    <path stroke-linecap="round" d="M12 7v5l3 3" />
                                                </svg>
                                            @endif
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-secondary">{{ $reminder->item->name }}</p>
                                            <p class="text-sm text-slate-500 mb-2">
                                                Tenggat :
                                                @if ($daysDiff === -1) Kemarin
                                                @elseif ($daysDiff === 0) Hari ini
                                                @elseif ($daysDiff === 1) Besok
                                                @else {{ $endDate->translatedFormat('j F Y') }}
                                                @endif
                                            </p>

                                            <div class="flex items-center gap-3 flex-wrap">
                                                <span
                                                    class="{{ $isOverdue ? 'bg-danger' : 'bg-warning' }} text-white text-xs font-semibold px-3 py-1 rounded-full">
                                                    @if ($isOverdue) Terlambat
                                                    @elseif ($daysDiff === 1) Tersisa 1 hari
                                                    @elseif ($daysDiff === 0) Jatuh tempo hari ini
                                                    @else Tersisa {{ $daysDiff }} hari
                                                    @endif
                                                </span>

                                                <a href="{{ route('items.show', $reminder->item) }}"
                                                    class="text-sm font-semibold text-secondary hover:underline">
                                                    Lihat Barang
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-slate-500 text-center py-8 text-sm">Tidak ada pengingat saat ini.</p>
                                @endforelse
                            </div>

                            <a href="{{ route('transactions.index') }}"
                                class="block text-center bg-slate-50 text-secondary font-semibold py-3 sm:py-4 text-sm sm:text-base hover:bg-slate-100 transition shrink-0">
                                Lihat semua notifikasi
                            </a>
                        </div>
                    </div>

                    {{-- Dropdown Profil --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="flex items-center gap-3" aria-label="Profil">
                            <div class="text-right leading-tight hidden sm:block">
                                <p class="font-semibold text-secondary text-sm">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ auth()->user()->isAdmin() ? 'Admin' : 'Mahasiswa' }}
                                </p>
                            </div>

                            <div
                                class="w-9 h-9 rounded-full bg-secondary text-white flex items-center justify-center font-semibold text-sm shrink-0">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        </button>

                        <div x-show="open" x-cloak @click.outside="open = false"
                            class="absolute right-0 mt-3 w-56 bg-white rounded-xl shadow-xl overflow-hidden z-50">

                            <div class="px-5 py-4 border-b border-slate-100">
                                <p class="font-semibold text-secondary">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-slate-400">{{ auth()->user()->nim_nidn }}</p>
                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-3 px-5 py-3.5 text-danger font-medium hover:bg-danger/5 transition"
                                    aria-label="Logout">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Konten halaman — padding bawah ekstra di mobile biar nggak ketutup bottom nav --}}
            <main class="flex-1 overflow-y-auto p-4 pb-24 sm:p-8 lg:pb-8">
                {{ $slot }}
            </main>
        </div>

        {{-- Bottom Navigation — CUMA muncul di mobile --}}
        <nav id="bottom-nav"
            class="lg:hidden fixed bottom-0 inset-x-0 bg-secondary border-t border-white/10 flex items-center justify-around px-2 z-40"
            style="height: calc(56px + env(safe-area-inset-bottom)); padding-bottom: env(safe-area-inset-bottom);">

            @foreach ($navItems as $item)
                @php $isActive = request()->routeIs($item['route']); @endphp
                <a href="{{ route($item['route']) }}" aria-label="{{ $item['label'] }}" class="flex flex-col items-center gap-1 px-3 py-1.5 rounded-lg transition
                                          {{ $isActive ? 'text-primary' : 'text-slate-400 hover:text-white' }}">

                    @if ($item['icon'] === 'grid')
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1.5" />
                            <rect x="14" y="3" width="7" height="7" rx="1.5" />
                            <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            <rect x="14" y="14" width="7" height="7" rx="1.5" />
                        </svg>
                    @elseif ($item['icon'] === 'box')
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8l-9-5-9 5 9 5 9-5z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8v8l9 5 9-5V8" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 13v8" />
                        </svg>
                    @elseif ($item['icon'] === 'clock')
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 9a9 9 0 111.5 8.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4v5h5" />
                        </svg>
                    @endif

                    <span class="text-[10px] font-semibold leading-none">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </div>
    {{-- Toast global --}}
    <div x-data="{ show: false, message: '', type: 'danger' }"
        x-on:toast.window="show = true; message = $event.detail.message; type = $event.detail.type ?? 'danger'; setTimeout(() => show = false, 3000)"
        x-show="show" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-4"
        class="fixed top-4 left-4 right-4 sm:left-auto sm:right-4 sm:w-96 z-100">
        <div class="rounded-xl shadow-lg px-5 py-4 text-white font-medium text-sm flex items-center gap-3"
            :class="type === 'success' ? 'bg-success' : 'bg-danger'">
            <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" d="M12 8v5M12 16h.01" />
            </svg>
            <span x-text="message"></span>
        </div>
    </div>
</body>

</html>