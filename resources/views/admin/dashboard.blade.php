<x-app-layout title="Dashboard">
    <div>
        <h1 class="text-2xl font-bold text-secondary">Dashboard</h1>
        <p class="text-slate-500 text-sm mt-0.5 mb-6">Selamat datang kembali! Informasi terkini mengenai inventaris</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">

        {{-- Total Barang --}}
        <div class="bg-surface rounded-2xl shadow-sm p-6">
            <div class="flex items-start justify-between">
                <p class="text-sm text-slate-500">Total Barang</p>
                <div class="w-10 h-10 bg-secondary rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8l-9-5-9 5 9 5 9-5z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8v8l9 5 9-5V8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 13v8"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-secondary mt-3">{{ number_format($totalItems, 0, ',', '.') }}</p>
            @if ($totalItemsDelta !== null)
                <p class="text-xs text-slate-400 mt-2">
                    <span class="{{ $totalItemsDelta >= 0 ? 'text-success' : 'text-danger' }} font-semibold">
                        {{ $totalItemsDelta >= 0 ? '+' : '' }}{{ $totalItemsDelta }}%
                    </span>
                    dari bulan lalu
                </p>
            @endif
        </div>

        {{-- Pinjaman Aktif --}}
        <div class="bg-surface rounded-2xl shadow-sm p-6">
            <div class="flex items-start justify-between">
                <p class="text-sm text-slate-500">Pinjaman Aktif</p>
                <div class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 17l6-6 4 4 8-8"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 7h7v7"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-secondary mt-3">{{ $pinjamanAktif }}</p>
            @if ($pinjamanAktifDelta !== null)
                <p class="text-xs text-slate-400 mt-2">
                    <span class="{{ $pinjamanAktifDelta >= 0 ? 'text-success' : 'text-danger' }} font-semibold">
                        {{ $pinjamanAktifDelta >= 0 ? '+' : '' }}{{ $pinjamanAktifDelta }}%
                    </span>
                    dari bulan lalu
                </p>
            @endif
        </div>

        {{-- Permintaan Tertunda --}}
        <div class="bg-surface rounded-2xl shadow-sm p-6">
            <div class="flex items-start justify-between">
                <p class="text-sm text-slate-500">Permintaan Tertunda</p>
                <div class="w-10 h-10 bg-warning rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v4a1 1 0 001 1h4"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6M9 17h6"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-secondary mt-3">{{ $permintaanTertunda }}</p>
            @if ($permintaanTertundaDelta !== null)
                <p class="text-xs text-slate-400 mt-2">
                    <span class="{{ $permintaanTertundaDelta >= 0 ? 'text-danger' : 'text-success' }} font-semibold">
                        {{ $permintaanTertundaDelta >= 0 ? '+' : '' }}{{ $permintaanTertundaDelta }}%
                    </span>
                    dari bulan lalu
                </p>
            @endif
        </div>
    </div>

    {{-- Aktivitas Terkini + Pengembalian Terlambat --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Aktivitas Terkini --}}
        <div class="lg:col-span-2 bg-surface rounded-2xl shadow-sm p-6">
            <h2 class="font-bold text-secondary mb-4">Aktivitas Terkini</h2>

            @if ($recentActivities->isEmpty())
                <p class="text-sm text-slate-400 py-6 text-center">Belum ada aktivitas.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-slate-400 border-b border-slate-100">
                            <tr>
                                <th class="py-2.5 pr-4 font-medium">User (NIM)</th>
                                <th class="py-2.5 pr-4 font-medium">Barang</th>
                                <th class="py-2.5 pr-4 font-medium">Status</th>
                                <th class="py-2.5 pr-4 font-medium">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recentActivities as $activity)
                                <tr class="border-b border-slate-50 last:border-0">
                                    <td class="py-3 pr-4">
                                        <p class="font-semibold text-secondary">{{ $activity->user_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $activity->user_nim }}</p>
                                    </td>
                                    <td class="py-3 pr-4 text-slate-600">{{ $activity->item_name }}</td>
                                    <td class="py-3 pr-4">
                                        @if ($activity->is_return)
                                            <span class="inline-flex items-center gap-1 bg-success/10 text-success text-xs font-semibold px-3 py-1 rounded-full">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7l10 10M17 7v10H7"/>
                                                </svg>
                                                Kembali
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-danger/10 text-danger text-xs font-semibold px-3 py-1 rounded-full">
                                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M7 7h10v10"/>
                                                </svg>
                                                Pinjam
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4 text-slate-400 whitespace-nowrap">{{ $activity->waktu }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Pengembalian Terlambat --}}
        <div class="bg-surface rounded-2xl shadow-sm p-6">
            <h2 class="font-bold text-secondary mb-4 flex items-center gap-2">
                <svg class="w-4.5 h-4.5 text-warning" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14.18A1.5 1.5 0 003.5 20.5h17a1.5 1.5 0 001.39-2.46L13.71 3.86a1.5 1.5 0 00-2.42 0z"/>
                </svg>
                Pengembalian Terlambat
            </h2>

            @if ($overdueReturns->isEmpty())
                <p class="text-sm text-slate-400 py-6 text-center">Tidak ada pengembalian yang telat.</p>
            @else
                <div class="space-y-3">
                    @foreach ($overdueReturns as $overdue)
                        <div class="border-l-4 {{ $overdue->severity === 'danger' ? 'border-danger bg-danger/5' : 'border-warning bg-warning/5' }} rounded-r-xl px-4 py-3">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-semibold text-secondary text-sm">{{ $overdue->user_name }}</p>
                                <span class="{{ $overdue->severity === 'danger' ? 'text-danger' : 'text-warning' }} text-xs font-semibold whitespace-nowrap flex items-center gap-1">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <circle cx="12" cy="12" r="9"/>
                                        <path stroke-linecap="round" d="M12 7v5l3 2"/>
                                    </svg>
                                    {{ $overdue->days_late }} hari telat
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">NIM: {{ $overdue->user_nim }} &bull; {{ $overdue->item_name }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>