<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Sistem Peminjaman Barang Kampus INSTIKI' }}</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')
  @stack('scripts')
</head>

<body class="bg-background">
  <div class="flex h-screen overflow-hidden">

    {{-- Sidebar — fixed, tidak ikut scroll --}}
    <aside class="w-64 bg-secondary text-white flex flex-col shrink-0 h-screen overflow-y-auto">
      <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
        <img src="{{ asset('images/logo-instiki.png') }}" alt="Logo" class="w-8 h-8 object-contain">
        <div>
          <p class="font-bold leading-tight">INSTIKI</p>
          <p class="text-xs text-slate-400 leading-tight">Sistem Peminjaman</p>
        </div>
      </div>

      {{-- Layout ini khusus ADMIN. Untuk sisi mahasiswa/dosen, lihat <x-app-layout>. --}}
        <nav class="flex-1 px-4 py-6 space-y-1">
          @php
            $navItems = [
              ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'grid'],
              ['route' => 'admin.items.index', 'label' => 'Inventaris', 'icon' => 'box'],
              ['route' => 'admin.transactions.index', 'label' => 'Permintaan', 'icon' => 'file'],
              ['route' => 'admin.calendar', 'label' => 'Kalender', 'icon' => 'clock'],
              ['route' => 'admin.reports.index', 'label' => 'Laporan', 'icon' => 'chart'],
              ['route' => 'admin.users.index', 'label' => 'Kelola User', 'icon' => 'users'],
            ];
          @endphp

          @foreach ($navItems as $item)
            @continue (!\Illuminate\Support\Facades\Route::has($item['route']))
            @php $isActive = request()->routeIs($item['route']); @endphp
            <a href="{{ route($item['route']) }}"
              class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition
                                                {{ $isActive ? 'bg-primary text-white' : 'text-slate-300 hover:bg-white/5' }}">

              @if ($item['icon'] === 'grid')
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="3" width="7" height="7" rx="1.5" />
                  <rect x="14" y="3" width="7" height="7" rx="1.5" />
                  <rect x="3" y="14" width="7" height="7" rx="1.5" />
                  <rect x="14" y="14" width="7" height="7" rx="1.5" />
                </svg>
              @elseif ($item['icon'] === 'box')
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21 8l-9-5-9 5 9 5 9-5z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 8v8l9 5 9-5V8" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 13v8" />
                </svg>
              @elseif ($item['icon'] === 'clock')
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 9a9 9 0 111.5 8.5" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 4v5h5" />
                </svg>
              @elseif ($item['icon'] === 'file')
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v4a1 1 0 001 1h4" />
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6M9 17h6" />
                </svg>
              @elseif ($item['icon'] === 'chart')
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V10M12 19V5M20 19v-7" />
                </svg>
              @elseif ($item['icon'] === 'users')
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 20v-1.5a3.5 3.5 0 00-3.5-3.5h-5A3.5 3.5 0 005 18.5V20" />
                  <circle cx="9.5" cy="7.5" r="3.5" />
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16 8a3 3 0 010 5.7M19.5 20v-1.3a3.3 3.3 0 00-2-3" />
                </svg>
              @endif

              {{ $item['label'] }}
            </a>
          @endforeach
        </nav>
    </aside>

    {{-- Main area --}}
    <div class="flex-1 flex flex-col h-screen overflow-hidden">

      {{-- Navbar — fixed, tidak ikut scroll --}}
      <header class="bg-white px-8 py-4 flex items-center justify-end gap-4 border-b border-slate-100 shrink-0">

        {{-- Dropdown Notifikasi --}}
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open" class="relative text-slate-500">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            @if ($reminders->count() > 0)
              <span class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-primary rounded-full"></span>
            @endif
          </button>

          <div x-show="open" x-cloak @click.outside="open = false"
            class="absolute right-0 mt-3 w-96 bg-white rounded-2xl shadow-xl overflow-hidden z-50">

            <div class="bg-secondary px-6 py-5">
              <h3 class="text-white font-bold text-lg">Pengingat Pengembalian</h3>
              <p class="text-slate-300 text-sm mt-0.5">{{ $reminders->count() }} barang perlu perhatian
              </p>
            </div>

            <div class="max-h-96 overflow-y-auto">
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
                      <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                      </svg>
                    @else
                      <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 7v5l3 3" />
                      </svg>
                    @endif
                  </div>

                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-secondary">{{ $reminder->item->name }}</p>
                    <p class="text-xs text-slate-400 -mt-1 mb-1 truncate">
                      {{ $reminder->user->name }} · {{ $reminder->user->nim_nidn }}
                    </p>
                    <p class="text-sm text-slate-500 mb-2">
                      Tenggat :
                      @if ($daysDiff === -1) Kemarin
                      @elseif ($daysDiff === 0) Hari ini
                      @elseif ($daysDiff === 1) Besok
                      @else {{ $endDate->translatedFormat('j F Y') }}
                      @endif
                    </p>

                    <div class="flex items-center gap-3">
                      <span
                        class="{{ $isOverdue ? 'bg-danger' : 'bg-warning' }} text-white text-xs font-semibold px-3 py-1 rounded-full">
                        @if ($isOverdue) Terlambat
                        @elseif ($daysDiff === 1) Tersisa 1 hari
                        @elseif ($daysDiff === 0) Jatuh tempo hari ini
                        @else Tersisa {{ $daysDiff }} hari
                        @endif
                      </span>

                      <a href="{{ route('admin.transactions.index') }}"
                        class="text-sm font-semibold text-secondary hover:underline">
                        Lihat Permintaan
                      </a>
                    </div>
                  </div>
                </div>
              @empty
                <p class="text-slate-500 text-center py-8 text-sm">Tidak ada pengingat saat ini.</p>
              @endforelse
            </div>

            <a href="{{ route('transactions.index') }}"
              class="block text-center bg-slate-50 text-secondary font-semibold py-4 hover:bg-slate-100 transition">
              Lihat semua notifikasi
            </a>
          </div>
        </div>

        {{-- Dropdown Profil --}}
        <div x-data="{ open: false }" class="relative">
          <button @click="open = !open" class="flex items-center gap-3">
            <div class="text-right leading-tight">
              <p class="font-semibold text-secondary text-sm">{{ auth()->user()->name }}</p>
              <p class="text-xs text-slate-400">Admin</p>
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
                class="w-full flex items-center gap-3 px-5 py-3.5 text-danger font-medium hover:bg-danger/5 transition">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
              </button>
            </form>
          </div>
        </div>
      </header>

      {{-- Konten halaman — INI yang scroll --}}
      <main class="flex-1 overflow-y-auto p-8">
        {{ $slot }}
      </main>
    </div>
  </div>
</body>

</html>