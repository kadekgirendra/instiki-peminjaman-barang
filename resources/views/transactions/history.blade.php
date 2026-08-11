<x-app-layout>
    <h1 class="text-xl sm:text-2xl font-bold text-secondary mb-4 sm:mb-6">History</h1>

    {{-- ============ VERSI DESKTOP — tabel, TIDAK berubah ============ --}}
    <div class="hidden lg:block bg-surface rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-secondary text-white">
                    <th class="text-left px-8 py-5 font-semibold">Barang</th>
                    <th class="text-left px-8 py-5 font-semibold">Tanggal</th>
                    <th class="text-left px-8 py-5 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($history as $loanRequestId => $group)
                    @php
                        $firstItem = $group->first();
                        $itemNames = $group->pluck('item.name')->implode(', ');
                        $isRejected = $firstItem->status === 'rejected';
                    @endphp
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-8 py-5 text-secondary">{{ $itemNames }}</td>
                        <td class="px-8 py-5 text-slate-500">{{ $firstItem->updated_at->translatedFormat('j F Y') }}</td>
                        <td class="px-8 py-5">
                            @if ($isRejected)
                                <span class="bg-danger text-white text-sm font-semibold px-5 py-2 rounded-lg">
                                    Ditolak
                                </span>
                            @else
                                <span class="bg-secondary text-white text-sm font-semibold px-5 py-2 rounded-lg">
                                    Selesai
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-slate-500 py-10">
                            Belum ada riwayat peminjaman.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ============ VERSI MOBILE — card list ============ --}}
    <div class="lg:hidden space-y-3">
        @forelse ($history as $loanRequestId => $group)
            @php
                $firstItem = $group->first();
                $itemNames = $group->pluck('item.name')->implode(', ');
                $isRejected = $firstItem->status === 'rejected';
            @endphp

            <div class="bg-surface rounded-xl p-4 shadow-sm border border-slate-100">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <h3 class="font-semibold text-secondary text-sm leading-snug flex-1">{{ $itemNames }}</h3>

                    @if ($isRejected)
                        <span class="bg-danger text-white text-xs font-semibold px-3 py-1.5 rounded-lg shrink-0">
                            Ditolak
                        </span>
                    @else
                        <span class="bg-secondary text-white text-xs font-semibold px-3 py-1.5 rounded-lg shrink-0">
                            Selesai
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                    {{ $firstItem->updated_at->translatedFormat('j F Y') }}
                </p>
            </div>
        @empty
            <div class="bg-surface rounded-xl p-8 text-center border border-slate-100">
                <p class="text-slate-500 text-sm">Belum ada riwayat peminjaman.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
