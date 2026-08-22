<x-app-layout>
    <h1 class="text-xl sm:text-2xl font-bold text-secondary mb-4 sm:mb-6">Dashboard</h1>

    {{-- Stat cards — 3 kolom kompak bahkan di mobile --}}
    <div class="grid grid-cols-3 gap-2 sm:gap-6 mb-6 sm:mb-8">
        <div class="bg-surface rounded-xl shadow-sm p-3 sm:p-6">
            <div class="w-8 h-8 sm:w-11 sm:h-11 bg-slate-100 rounded-lg flex items-center justify-center mb-2 sm:mb-4">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <p class="text-xl sm:text-3xl font-bold text-secondary">{{ $activeLoans }}</p>
            <p class="text-slate-500 text-[11px] sm:text-sm mt-1 leading-tight">Pinjaman Aktif</p>
        </div>

        <div class="bg-surface rounded-xl shadow-sm p-3 sm:p-6">
            <div class="w-8 h-8 sm:w-11 sm:h-11 bg-warning/10 rounded-lg flex items-center justify-center mb-2 sm:mb-4">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-warning" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
                </svg>
            </div>
            <p class="text-xl sm:text-3xl font-bold text-secondary">{{ $pendingRequests }}</p>
            <p class="text-slate-500 text-[11px] sm:text-sm mt-1 leading-tight">Permintaan Tertunda</p>
        </div>

        <div class="bg-surface rounded-xl shadow-sm p-3 sm:p-6">
            <div class="w-8 h-8 sm:w-11 sm:h-11 bg-primary/10 rounded-lg flex items-center justify-center mb-2 sm:mb-4">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-xl sm:text-3xl font-bold text-secondary">{{ $totalHistory }}</p>
            <p class="text-slate-500 text-[11px] sm:text-sm mt-1 leading-tight">Total History</p>
        </div>
    </div>

    {{-- Quick Action --}}
    <div class="rounded-2xl p-5 sm:p-8 relative overflow-hidden"
        style="background: linear-gradient(135deg, #FE0000 0%, #A10303 100%);">

        {{-- Mobile: ikon di atas, stack vertikal --}}
        <div class="flex sm:hidden flex-col">
            <div class="w-11 h-11 bg-white/20 rounded-full flex items-center justify-center mb-4">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </div>
            <h2 class="text-white text-lg font-bold mb-1.5">Quick Action</h2>
            <p class="text-white/80 text-sm mb-4">Jelajahi katalog dan pinjam barang secara instan.</p>
            <a href="{{ route('items.index') }}"
                class="inline-block text-center bg-white text-primary font-semibold px-6 py-2.5 rounded-lg">
                Pinjam Barang
            </a>
        </div>

        {{-- Desktop: ikon di samping, stack horizontal --}}

        <div class="hidden sm:flex items-center justify-between">
            <div>
                <h2 class="text-white text-xl font-bold mb-2">Quick Action</h2>
                <p class="text-white/80 mb-5">Jelajahi katalog dan pinjam barang secara instan.</p>
                <a href="{{ route('items.index') }}"
                    class="inline-block bg-white text-primary font-semibold px-6 py-2.5 rounded-lg">
                    Pinjam Barang
                </a>
            </div>
            <div class="w-14 h-14 bg-white/20 rounded-full flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
            </div>
        </div>
    </div>
</x-app-layout>
