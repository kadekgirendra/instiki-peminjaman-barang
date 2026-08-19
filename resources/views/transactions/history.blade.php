<x-app-layout>
    <h1 class="text-xl sm:text-2xl font-bold text-secondary mb-4 sm:mb-6">History</h1>

    <div class="space-y-3" x-data="{ openId: null }">
        @forelse ($history as $loanRequestId => $group)
            @php
                $firstItem = $group->first();
                $isRejected = $firstItem->status === 'rejected';
                $totalFee = $group->sum('total_fee');
                $isLate = $group->contains(fn($t) => $t->is_overdue);
                $itemNames = $group->pluck('item.name')->implode(', ');
            @endphp

            <div class="bg-surface rounded-2xl shadow-sm border border-slate-100 overflow-hidden">

                {{-- Header — klik untuk expand --}}
                <button type="button" @click="openId = openId === {{ $loanRequestId }} ? null : {{ $loanRequestId }}"
                    class="w-full flex items-center gap-3 sm:gap-4 p-4 sm:p-5 text-left hover:bg-slate-50 transition">

                    {{-- Ikon status --}}
                    <div
                        class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center shrink-0
                                {{ $isRejected ? 'bg-danger/10' : 'bg-success/10' }}">
                        @if ($isRejected)
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-danger" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        @else
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-success" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-secondary text-sm sm:text-base truncate">{{ $itemNames }}</p>
                        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                            {{ $firstItem->start_date->translatedFormat('j M') }} –
                            {{ $firstItem->end_date->translatedFormat('j M Y') }}
                            <span class="mx-1">•</span>
                            {{ $group->count() }} barang
                        </p>
                    </div>

                    {{-- Badge status — pil datar, bukan tombol --}}
                    <div class="flex items-center gap-2 shrink-0">
                        @if ($isRejected)
                            <span class="bg-danger text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                                Ditolak
                            </span>
                        @else
                            <span class="bg-success text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                                Selesai
                            </span>
                        @endif

                        @if (!$isRejected && $isLate)
                            <span
                                class="hidden sm:inline-flex bg-warning text-white text-xs font-semibold px-3 py-1.5 rounded-full">
                                Telat
                            </span>
                        @endif

                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                            :class="openId === {{ $loanRequestId }} ? 'rotate-180' : ''" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                {{-- Detail — expand --}}
                <div x-show="openId === {{ $loanRequestId }}" x-collapse x-cloak
                    class="border-t border-slate-100 px-4 sm:px-5 py-4 sm:py-5 bg-slate-50/50">

                    {{-- Badge telat, versi mobile (di dalam detail biar nggak sesak di header) --}}
                    @if (!$isRejected && $isLate)
                        <span
                            class="sm:hidden inline-flex bg-warning text-white text-xs font-semibold px-3 py-1.5 rounded-full mb-3">
                            Telat mengembalikan
                        </span>
                    @endif

                    {{-- Daftar barang --}}
                    <div class="space-y-2 mb-4">
                        @foreach ($group as $transaction)
                            <div
                                class="flex items-center justify-between bg-white rounded-lg px-3 sm:px-4 py-2.5 border border-slate-100">
                                <div>
                                    <p class="font-medium text-secondary text-sm">{{ $transaction->item->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $transaction->item->category }}</p>
                                </div>
                                <span
                                    class="text-xs sm:text-sm text-slate-500 font-medium">{{ $transaction->quantity }}
                                    unit</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Info tanggal & denda --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
                        <div class="bg-white rounded-lg p-3 border border-slate-100">
                            <p class="text-xs text-slate-500 mb-0.5">Tanggal Pinjam</p>
                            <p class="text-sm font-semibold text-secondary">
                                {{ $firstItem->start_date->translatedFormat('j M Y') }}</p>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-slate-100">
                            <p class="text-xs text-slate-500 mb-0.5">Batas Kembali</p>
                            <p class="text-sm font-semibold text-secondary">
                                {{ $firstItem->end_date->translatedFormat('j M Y') }}</p>
                        </div>
                        @if ($firstItem->returned_at)
                            <div class="bg-white rounded-lg p-3 border border-slate-100">
                                <p class="text-xs text-slate-500 mb-0.5">Dikembalikan</p>
                                <p class="text-sm font-semibold text-secondary">
                                    {{ $firstItem->returned_at->translatedFormat('j M Y') }}</p>
                            </div>
                        @endif
                        @if ($totalFee > 0)
                            <div class="bg-danger/5 rounded-lg p-3 border border-danger/20">
                                <p class="text-xs text-danger/70 mb-0.5">Total Denda</p>
                                <p class="text-sm font-bold text-danger">Rp {{ number_format($totalFee, 0, ',', '.') }}
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Catatan pengembalian, kalau ada --}}
                    @if ($firstItem->return_note)
                        <div class="bg-white rounded-lg p-3 sm:p-4 border border-slate-100 mb-4">
                            <p class="text-xs text-slate-500 mb-1">Catatan</p>
                            <p class="text-sm text-secondary">{{ $firstItem->return_note }}</p>
                        </div>
                    @endif

                    {{-- Bukti foto pengembalian, kalau ada --}}
                    @if ($firstItem->return_photo)
                        <a href="{{ asset('storage/' . $firstItem->return_photo) }}" target="_blank"
                            class="inline-flex items-center gap-2 text-primary text-sm font-semibold hover:underline">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21" />
                            </svg>
                            Lihat bukti foto pengembalian
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-surface rounded-2xl p-10 sm:p-12 text-center border border-slate-100">
                <div class="w-14 h-14 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.5 9a9 9 0 111.5 8.5" />
                    </svg>
                </div>
                <p class="text-slate-500 text-sm">Belum ada riwayat peminjaman.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
