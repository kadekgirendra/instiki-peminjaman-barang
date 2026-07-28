<x-app-layout>
    <div x-data="{ showSuccessModal: {{ session('loan_success') ? 'true' : 'false' }} }">

        <h1 class="text-2xl font-bold text-secondary mb-6">Pinjaman Saya</h1>

        {{-- Pinjaman Aktif --}}
        <div class="bg-success/5 border border-success/20 rounded-2xl p-6 mb-8">
            <h2 class="text-xl font-bold text-secondary">Pinjaman Aktif</h2>
            <p class="text-slate-500 text-sm mb-5">Barang saat ini</p>

            <div class="space-y-4">
                @forelse ($activeLoanGroups as $loanRequestId => $group)
                    @php
                        $firstItem = $group->first();
                        $itemNames = $group->pluck('item.name')->implode(', ');
                        $alreadyRequested = $firstItem->return_requested_at !== null;
                    @endphp

                    <div class="bg-surface rounded-xl p-5 flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="font-semibold text-secondary">{{ Str::limit($itemNames, 40) }}</h3>
                                <span class="bg-success text-white text-xs font-semibold px-3 py-1 rounded-full">
                                    Dipinjam
                                </span>
                            </div>
                            <p class="text-sm text-slate-500">
                                Dipinjam : <span
                                    class="font-medium text-secondary">{{ $firstItem->start_date->translatedFormat('j M Y') }}</span>
                                &nbsp;&nbsp;
                                Dikembalikan : <span
                                    class="font-medium text-secondary">{{ $firstItem->end_date->translatedFormat('j M Y') }}</span>
                            </p>
                        </div>

                        @if ($alreadyRequested)
                            <span class="bg-warning/10 text-warning text-sm font-semibold px-4 py-2 rounded-lg">
                                Menunggu Konfirmasi Admin
                            </span>
                        @else
                            <a href="{{ route('returns.create', $loanRequestId) }}"
                                class="bg-secondary text-white font-semibold px-6 py-2.5 rounded-lg hover:opacity-90 transition">
                                Kembalikan
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="text-slate-500 text-center py-6">Tidak ada barang yang sedang dipinjam.</p>
                @endforelse
            </div>
        </div>

        {{-- Permintaan --}}
        <div>
            <h2 class="text-xl font-bold text-secondary mb-4">Permintaan</h2>

            <div class="space-y-3">
                @forelse ($requests as $request)
                    @php
                        $statusMap = [
                            'pending' => ['label' => 'Tertunda', 'class' => 'bg-warning text-white', 'note' => 'Tunggu disetujui'],
                            'booked' => ['label' => 'Disetujui', 'class' => 'bg-info text-white', 'note' => 'Siap diambil'],
                            'rejected' => ['label' => 'Ditolak', 'class' => 'bg-danger text-white', 'note' => 'Permintaan ditolak'],
                        ];
                        $status = $statusMap[$request->status];
                    @endphp

                    <div
                        class="bg-surface rounded-xl p-5 flex items-center justify-between flex-wrap gap-4 border border-slate-100">
                        <div>
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="font-semibold text-secondary">{{ $request->item->name }}</h3>
                                <span class="{{ $status['class'] }} text-xs font-semibold px-3 py-1 rounded-full">
                                    {{ $status['label'] }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-500">
                                Diminta : {{ $request->created_at->translatedFormat('j M Y') }}
                            </p>
                        </div>

                        <span class="text-sm text-slate-400 italic">{{ $status['note'] }}</span>
                    </div>
                @empty
                    <p class="text-slate-500 text-center py-6">Belum ada permintaan peminjaman.</p>
                @endforelse
            </div>
        </div>

        {{-- Modal Sukses --}}
        <div x-show="showSuccessModal" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-6">
            <div class="bg-white rounded-2xl p-8 w-full max-w-md text-center">
                <div class="w-16 h-16 bg-success/10 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-7 h-7 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-secondary mb-2">Pengajuan Berhasil Dikirim!</h2>
                <p class="text-slate-500 mb-6">Silakan cek status pada halaman Pinjaman</p>

                <div class="bg-slate-50 rounded-xl p-5 text-left mb-6">
                    <p class="font-semibold text-secondary mb-3">{{ session('loan_items_summary') }}</p>
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