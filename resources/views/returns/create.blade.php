<x-app-layout>
    <div class="flex items-center gap-3 mb-4 sm:mb-6">
        <a href="{{ route('transactions.index') }}"
            class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg border border-slate-200 bg-surface text-secondary hover:bg-slate-50 transition shrink-0"
            aria-label="Kembali ke Pinjaman Saya">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-secondary">Pengembalian Barang</h1>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-danger/10 border border-danger/30 text-danger text-sm px-4 py-3 rounded-lg">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 pb-24 lg:pb-8">

        {{-- Form utama --}}
        <div class="lg:col-span-2 bg-surface rounded-2xl shadow-sm p-4 sm:p-8">
            <form method="POST" action="{{ route('returns.store', $loanRequest) }}" enctype="multipart/form-data"
                class="space-y-5 sm:space-y-6" id="returnForm">
                @csrf

                <div x-data="{ fileName: null }">
                    <label class="block font-semibold text-secondary mb-1">Unggah Bukti Foto *</label>

                    <label x-show="!fileName" @dragover.prevent @dragleave.prevent
                        @drop.prevent="
                            $refs.photoInput.files = $event.dataTransfer.files;
                            fileName = $event.dataTransfer.files[0]?.name;
                        "
                        class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 rounded-xl py-10 sm:py-14 px-4 cursor-pointer hover:bg-slate-50 transition text-center">

                        <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 text-secondary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                            </svg>
                        </div>
                        <p class="text-secondary font-medium text-sm sm:text-base">
                            <span class="hidden sm:inline">Upload photo of the item at the return location</span>
                            <span class="sm:hidden">Tap untuk unggah foto bukti</span>
                        </p>
                        <p class="text-slate-400 text-xs sm:text-sm mt-1">JPG/PNG, max 5MB</p>

                        <input type="file" name="photo" required class="hidden" accept=".jpg,.jpeg,.png"
                            x-ref="photoInput" @change="fileName = $event.target.files[0]?.name">
                    </label>

                    <div x-show="fileName" x-cloak
                        class="flex items-center gap-3 sm:gap-4 border-2 border-success/30 bg-success/5 rounded-xl p-3 sm:p-4">
                        <div
                            class="w-10 h-10 sm:w-12 sm:h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21" />
                            </svg>
                        </div>
                        <p class="font-medium text-secondary text-sm sm:text-base truncate flex-1" x-text="fileName">
                        </p>
                        <button type="button" @click="fileName = null; $refs.photoInput.value = ''"
                            class="text-slate-400 hover:text-danger p-2 -m-2 shrink-0" aria-label="Hapus file">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-secondary mb-1">Catatan (Optional)</label>
                    <textarea name="note" rows="4" placeholder="Tambahkan informasi tambahan..."
                        class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary text-sm sm:text-base">{{ old('note') }}</textarea>
                </div>

                {{-- Tombol submit — versi normal, CUMA tampil di desktop --}}
                <button type="submit"
                    class="hidden lg:block w-full bg-primary text-white font-semibold py-3.5 rounded-xl hover:opacity-90 transition">
                    Kirim Pengembalian
                </button>
            </form>
        </div>

        {{-- Sidebar kanan — Ringkasan Barang --}}
        <div>
            <div class="bg-surface rounded-2xl shadow-sm p-5 sm:p-6">
                <h2 class="text-base sm:text-lg font-bold text-secondary mb-4 sm:mb-5">Ringkasan Barang</h2>

                <div class="mb-5">
                    <p class="text-xs sm:text-sm text-slate-500 mb-1.5">Barang</p>
                    <div class="space-y-1.5">
                        @foreach ($transactions as $transaction)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary shrink-0"></span>
                                <span class="text-secondary font-medium">{{ $transaction->item->name }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs sm:text-sm text-slate-500">Tanggal Pinjam</span>
                        <span
                            class="font-semibold text-secondary text-sm">{{ $transactions->first()->start_date->translatedFormat('j M Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs sm:text-sm text-slate-500">Tanggal Pengembalian</span>
                        <span
                            class="font-semibold text-secondary text-sm">{{ $transactions->first()->end_date->translatedFormat('j M Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs sm:text-sm text-slate-500">Jumlah Barang</span>
                        <span class="font-semibold text-secondary text-sm">{{ $transactions->count() }} item</span>
                    </div>
                </div>

                <div class="mt-5 bg-slate-50 border border-slate-200 rounded-xl p-3.5 flex gap-2.5">
                    <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 8h.01M11 12h1v4h1" />
                    </svg>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Pastikan foto bukti diambil di lokasi pengembalian yang jelas.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tombol submit — sticky bottom bar, CUMA tampil di mobile --}}
    <div class="lg:hidden fixed inset-x-0 bg-white border-t border-slate-200 p-4 z-30"
        style="bottom: calc(56px + env(safe-area-inset-bottom));">
        <button type="submit" form="returnForm"
            class="w-full bg-primary text-white font-semibold py-3.5 rounded-xl">
            Kirim Pengembalian
        </button>
    </div>
</x-app-layout>
