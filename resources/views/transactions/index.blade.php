<x-app-layout>
    <div x-data="{ showSuccessModal: {{ session('loan_success') ? 'true' : 'false' }} }">

        <h1 class="text-xl sm:text-2xl font-bold text-secondary mb-4 sm:mb-6">Pinjaman Saya</h1>

        {{-- Pinjaman Aktif --}}
        <div class="bg-success/5 border border-success/20 rounded-2xl p-4 sm:p-6 mb-6 sm:mb-8">
            <h2 class="text-lg sm:text-xl font-bold text-secondary">Pinjaman Aktif</h2>
            <p class="text-slate-500 text-sm mb-4 sm:mb-5">Barang saat ini</p>

            <div class="space-y-3 sm:space-y-4">
                @forelse ($activeLoanGroups as $loanRequestId => $group)
                    @php
                        $firstItem = $group->first();
                        $itemNames = $group->pluck('item.name')->implode(', ');
                        $alreadyRequested = $firstItem->return_requested_at !== null;
                    @endphp

                    <div class="bg-surface rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 sm:gap-3 mb-2 flex-wrap">
                                <h3 class="font-semibold text-secondary text-sm sm:text-base">{{ Str::limit($itemNames, 40) }}</h3>
                                <span class="bg-success text-white text-xs font-semibold px-3 py-1 rounded-full shrink-0">
                                    Dipinjam
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500">
                                Dipinjam : <span class="font-medium text-secondary">{{ $firstItem->start_date->translatedFormat('j M Y') }}</span>
                                <br class="sm:hidden">
                                <span class="hidden sm:inline">&nbsp;&nbsp;</span>
                                Dikembalikan : <span class="font-medium text-secondary">{{ $firstItem->end_date->translatedFormat('j M Y') }}</span>
                            </p>
                        </div>

                        @if ($alreadyRequested)
                            <span class="bg-warning/10 text-warning text-sm font-semibold px-4 py-2.5 sm:py-2 rounded-lg text-center shrink-0">
                                Menunggu Konfirmasi Admin
                            </span>
                        @else
                            <a href="{{ route('returns.create', $loanRequestId) }}"
                                class="bg-secondary text-white font-semibold px-6 py-2.5 rounded-lg hover:opacity-90 transition text-center shrink-0">
                                Kembalikan
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="text-slate-500 text-center py-6 text-sm sm:text-base">Tidak ada barang yang sedang dipinjam.</p>
                @endforelse
            </div>
        </div>

        {{-- Permintaan --}}
        <div>
            <h2 class="text-lg sm:text-xl font-bold text-secondary mb-3 sm:mb-4">Permintaan</h2>

            <div class="space-y-2.5 sm:space-y-3">
                @forelse ($requests as $request)
                    @php
                        $statusMap = [
                            'pending' => ['label' => 'Tertunda', 'class' => 'bg-warning text-white', 'note' => 'Tunggu disetujui'],
                            'booked' => ['label' => 'Disetujui', 'class' => 'bg-info text-white', 'note' => 'Siap diambil'],
                            'rejected' => ['label' => 'Ditolak', 'class' => 'bg-danger text-white', 'note' => 'Permintaan ditolak'],
                        ];
                        $status = $statusMap[$request->status];
                    @endphp

                    <div class="bg-surface rounded-xl p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-4 border border-slate-100">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 sm:gap-3 mb-1 flex-wrap">
                                <h3 class="font-semibold text-secondary text-sm sm:text-base">{{ $request->item->name }}</h3>
                                <span class="{{ $status['class'] }} text-xs font-semibold px-3 py-1 rounded-full shrink-0">
                                    {{ $status['label'] }}
                                </span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500">
                                Diminta : {{ $request->created_at->translatedFormat('j M Y') }}
                            </p>
                        </div>

                        <span class="text-xs sm:text-sm text-slate-400 italic shrink-0">{{ $status['note'] }}</span>
                    </div>
                @empty
                    <p class="text-slate-500 text-center py-6 text-sm sm:text-base">Belum ada permintaan peminjaman.</p>
                @endforelse
            </div>
        </div>

        {{-- Modal Sukses --}}
        <div x-show="showSuccessModal" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4 sm:p-6">
            <div class="bg-white rounded-2xl p-6 sm:p-8 w-full max-w-md text-center">
                <div class="w-14 h-14 sm:w-16 sm:h-16 bg-success/10 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-5">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h2 class="text-xl sm:text-2xl font-bold text-secondary mb-2">Pengajuan Berhasil Dikirim!</h2>
                <p class="text-sm sm:text-base text-slate-500 mb-5 sm:mb-6">Silakan cek status pada halaman Pinjaman</p>

                <div class="bg-slate-50 rounded-xl p-4 sm:p-5 text-left mb-5 sm:mb-6">
                    <p class="font-semibold text-secondary mb-3 text-sm sm:text-base">{{ session('loan_items_summary') }}</p>
                    <div class="border-t border-slate-200 pt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tanggal Peminjaman</span>
                            <span class="font-medium text-secondary">{{ session('loan_start_date') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tanggal Kembali</span>
                            <span class="font-medium text-secondary">{{ session('loan_end_date') }}</span>
                        </div>
                    </div>
                </div>

                <button type="button" @click="showSuccessModal = false"
                    class="w-full bg-success text-white font-semibold py-3 rounded-xl">
                    Selesai
                </button>
            </div>
        </div>

    </div>
</x-app-layout>