<x-admin-layout title="Permintaan">
    <div x-data="{ selected: null }">

        <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-secondary">Permintaan</h1>
                <p class="text-slate-500 text-sm mt-0.5">Kelola permintaan peminjaman dari mahasiswa dan dosen</p>
            </div>

            <form method="GET">
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="M21 21l-3.5-3.5" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" onchange="this.form.submit()"
                        placeholder="Search items..."
                        class="pl-10 pr-4 py-2.5 w-64 rounded-full border-0 bg-white shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                </div>
            </form>
        </div>

        @if (session('success'))
            <div class="bg-success/10 text-success border border-success/20 rounded-xl px-5 py-3 mb-6 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-danger/10 text-danger border border-danger/20 rounded-xl px-5 py-3 mb-6 text-sm font-medium">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-surface rounded-2xl shadow-sm overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-secondary text-white">
                    <tr>
                        <th class="py-4 px-6 font-semibold">Pengguna</th>
                        <th class="py-4 px-6 font-semibold">Barang</th>
                        <th class="py-4 px-6 font-semibold">Tanggal Permintaan</th>
                        <th class="py-4 px-6 font-semibold">Tanggal Peminjaman</th>
                        <th class="py-4 px-6 font-semibold">Tanggal Pengembalian</th>
                        <th class="py-4 px-6 font-semibold">Status</th>
                        <th class="py-4 px-6 font-semibold">Denda</th>
                        <th class="py-4 px-6 font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $g)
                        <tr class="border-b border-slate-100 last:border-0 hover:bg-background/60 cursor-pointer transition"
                            @click="selected = {{ Illuminate\Support\Js::from($g) }}">
                            <td class="py-4 px-6">
                                <p class="font-semibold text-secondary">{{ $g['user_name'] }}</p>
                                <p class="text-xs text-info">{{ $g['user_nim'] }}</p>
                            </td>
                            <td class="py-4 px-6 text-slate-600 max-w-[180px] truncate" title="{{ $g['items_label'] }}">
                                {{ $g['items_label_short'] }}
                            </td>
                            <td class="py-4 px-6 text-slate-500 whitespace-nowrap">{{ $g['tanggal_permintaan'] }}</td>
                            <td class="py-4 px-6 text-slate-500 whitespace-nowrap">{{ $g['tanggal_pinjam'] }}</td>
                            <td class="py-4 px-6 text-slate-500 whitespace-nowrap">{{ $g['tanggal_kembali'] }}</td>
                            <td class="py-4 px-6">
                                <span
                                    class="{{ $g['status_badge'] }} text-xs font-semibold px-3.5 py-1.5 rounded-full whitespace-nowrap inline-block">
                                    {{ $g['status_label'] }}
                                </span>
                            </td>
                            <td class="py-4 px-6 whitespace-nowrap">
                                @if ($g['status'] === 'completed')
                                    <span class="font-semibold {{ $g['total_fine'] > 0 ? 'text-danger' : 'text-slate-400' }}">
                                        Rp{{ number_format($g['total_fine'], 0, ',', '.') }}
                                    </span>
                                    @if ($g['was_returned_late'])
                                        <span class="block text-xs text-danger/70">Telat {{ $g['days_late_at_return'] }} hari</span>
                                    @endif
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="py-4 px-6" onclick="event.stopPropagation()">
                                @if ($g['status'] === 'pending')
                                    <div class="flex items-center gap-2">
                                        <form method="POST" action="{{ $g['approve_url'] }}">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="flex items-center gap-1.5 bg-success text-white text-xs font-semibold px-4 py-2 rounded-full hover:opacity-90 transition">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Setujui
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ $g['reject_url'] }}"
                                            onsubmit="return confirm('Tolak permintaan ini?')">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                class="flex items-center gap-1.5 bg-danger text-white text-xs font-semibold px-4 py-2 rounded-full hover:opacity-90 transition">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="2.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Tolak
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($g['status'] === 'booked')
                                    <button type="button" @click.stop="selected = {{ Illuminate\Support\Js::from($g) }}"
                                        class="flex items-center gap-1.5 bg-secondary text-white text-xs font-semibold px-4 py-2 rounded-full hover:opacity-90 transition">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Selesaikan
                                    </button>
                                @else
                                    <span class="text-slate-400 text-xs">Tidak ada aksi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-slate-500 py-10">Belum ada permintaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modal Detail Permintaan --}}
        <div x-show="selected" x-cloak @click.self="selected = null" @keydown.escape.window="selected = null"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-6">
            <div class="bg-white rounded-3xl w-full max-w-xl shadow-xl max-h-[90vh] overflow-y-auto" @click.stop>
                <template x-if="selected">
                    <div>
                        <div class="flex items-center justify-between px-8 pt-7 pb-5 border-b border-slate-100">
                            <h3 class="text-xl font-bold text-secondary">Detail Permintaan</h3>
                            <button @click="selected = null" type="button"
                                class="text-slate-400 hover:text-secondary transition">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-8 py-6 space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-slate-400 mb-1">Nama Mahasiswa</p>
                                    <p class="font-medium text-secondary" x-text="selected.user_name"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 mb-1">NIM</p>
                                    <p class="font-medium text-secondary" x-text="selected.user_nim"></p>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400 mb-2">Barang</p>
                                <div class="border border-slate-200 rounded-xl overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead class="bg-background text-slate-500">
                                            <tr>
                                                <th class="text-left font-medium py-2 px-4">Nama Barang</th>
                                                <th class="text-right font-medium py-2 px-4">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(barang, idx) in (selected ? selected.items_list : [])"
                                                :key="idx">
                                                <tr class="border-t border-slate-100">
                                                    <td class="py-2 px-4 text-secondary font-medium"
                                                        x-text="barang.name"></td>
                                                    <td class="py-2 px-4 text-right text-slate-600"
                                                        x-text="barang.quantity + ' unit'"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-slate-400 mb-1">Tanggal Permintaan</p>
                                    <p class="font-medium text-secondary" x-text="selected.tanggal_permintaan"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 mb-1">Tanggal Pinjam</p>
                                    <p class="font-medium text-secondary" x-text="selected.tanggal_pinjam"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400 mb-1">Tanggal Pengembalian</p>
                                    <p class="font-medium text-secondary" x-text="selected.tanggal_kembali"></p>
                                </div>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400 mb-2">Status</p>
                                <span :class="selected.status_badge"
                                    class="text-xs font-semibold px-3.5 py-1.5 rounded-full"
                                    x-text="selected.status_label"></span>
                            </div>

                            <div x-show="selected.catatan">
                                <p class="text-xs text-slate-400 mb-2">Catatan</p>
                                <p class="bg-background rounded-xl px-4 py-3 text-sm text-slate-600"
                                    x-text="selected.catatan"></p>
                            </div>

                            <div x-show="selected.document_url">
                                <p class="text-xs text-slate-400 mb-2">Dokumen Pendukung</p>
                                <div
                                    class="flex items-center justify-between border border-slate-200 rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-6 h-6 text-info shrink-0" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14 3v4a1 1 0 001 1h4" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-secondary"
                                                x-text="selected.document_name"></p>
                                            <p class="text-xs text-slate-400">Klik untuk melihat</p>
                                        </div>
                                    </div>
                                    <a :href="selected.document_url" target="_blank"
                                        class="bg-primary text-white text-xs font-semibold px-4 py-2 rounded-full hover:opacity-90 transition">
                                        Lihat
                                    </a>
                                </div>
                            </div>

                            {{-- Form penyelesaian — muncul kalau status Disetujui (booked) --}}
                            <div x-show="selected.status === 'booked'" class="border-t border-slate-100 pt-6">
                                <h4 class="font-semibold text-secondary mb-4">Tandai Selesai</h4>

                                <div x-show="selected.is_overdue"
                                    class="bg-danger/5 border border-danger/20 rounded-xl px-4 py-3 mb-4 text-xs text-danger font-medium">
                                    <span
                                        x-text="'Barang ini sudah telat ' + selected.days_late + ' hari dari tanggal pengembalian.'"></span>
                                </div>

                                <div x-show="selected.has_return_request"
                                    class="bg-info/5 border border-info/20 rounded-xl px-4 py-3 mb-4 text-xs text-info">
                                    Mahasiswa sudah mengajukan pengembalian. Cek bukti di bawah sebelum menandai
                                    selesai.
                                </div>

                                <div x-show="selected.return_photo_url || selected.return_note"
                                    class="bg-background rounded-xl p-4 mb-4">
                                    <p class="text-xs font-semibold text-slate-500 mb-2">Bukti dari mahasiswa</p>
                                    <template x-if="selected.return_photo_url">
                                        <img :src="selected.return_photo_url"
                                            class="w-full h-40 object-cover rounded-lg mb-2">
                                    </template>
                                    <p class="text-sm text-slate-600" x-text="selected.return_note"></p>
                                </div>

                                <form method="POST" :action="selected ? selected.complete_url : ''" class="space-y-4">
                                    @csrf
                                    @method('PATCH')

                                    <div>
                                        <label class="block text-sm font-medium text-secondary mb-1.5">Tanggal Barang
                                            Kembali</label>
                                        <input type="date" name="returned_at" required
                                            :value="new Date().toISOString().slice(0,10)"
                                            class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-secondary mb-1.5">Denda per Barang
                                            (Rp) &mdash; isi kalau ada yang telat/rusak</label>
                                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                                            <template x-for="barang in (selected ? selected.items_list : [])"
                                                :key="barang.id">
                                                <div
                                                    class="flex items-center justify-between gap-3 px-4 py-2.5 border-b border-slate-100 last:border-0">
                                                    <span class="text-sm text-secondary"
                                                        x-text="barang.name + ' (x' + barang.quantity + ')'"></span>
                                                    <input type="number" :name="'fines[' + barang.id + ']'" min="0"
                                                        step="1000" value="0"
                                                        class="w-32 rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-right focus:outline-none focus:ring-2 focus:ring-primary/30">
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <button type="submit"
                                        class="w-full bg-success text-white font-semibold py-3 rounded-full hover:opacity-90 transition">
                                        Tandai Selesai
                                    </button>
                                </form>
                            </div>

                            {{-- Riwayat pengembalian — muncul kalau status Selesai (completed) --}}
                            <div x-show="selected.status === 'completed'" class="border-t border-slate-100 pt-6">
                                <h4 class="font-semibold text-secondary mb-4">Riwayat Pengembalian</h4>

                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-xs text-slate-400 mb-1">Tanggal Dikembalikan</p>
                                        <p class="font-medium text-secondary" x-text="selected.returned_at"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400 mb-1">Status Pengembalian</p>
                                        <p class="font-medium"
                                            :class="selected.was_returned_late ? 'text-danger' : 'text-success'"
                                            x-text="selected.was_returned_late ? ('Telat ' + selected.days_late_at_return + ' hari') : 'Tepat waktu'">
                                        </p>
                                    </div>
                                </div>

                                <p class="text-xs text-slate-400 mb-2">Rincian Denda per Barang</p>
                                <div class="border border-slate-200 rounded-xl overflow-hidden mb-3">
                                    <table class="w-full text-sm">
                                        <thead class="bg-background text-slate-500">
                                            <tr>
                                                <th class="text-left font-medium py-2 px-4">Barang</th>
                                                <th class="text-right font-medium py-2 px-4">Denda</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(barang, idx) in (selected ? selected.items_list : [])"
                                                :key="idx">
                                                <tr class="border-t border-slate-100">
                                                    <td class="py-2 px-4 text-secondary font-medium"
                                                        x-text="barang.name"></td>
                                                    <td class="py-2 px-4 text-right"
                                                        :class="barang.fine > 0 ? 'text-danger font-semibold' : 'text-slate-400'"
                                                        x-text="'Rp' + new Intl.NumberFormat('id-ID').format(barang.fine)">
                                                    </td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="flex items-center justify-between bg-background rounded-xl px-4 py-3">
                                    <span class="text-sm font-semibold text-secondary">Total Denda</span>
                                    <span class="text-sm font-bold"
                                        :class="selected.total_fine > 0 ? 'text-danger' : 'text-success'"
                                        x-text="'Rp' + new Intl.NumberFormat('id-ID').format(selected.total_fine)"></span>
                                </div>
                            </div>
                        </div>

                        <div x-show="selected.status === 'pending'" class="flex justify-end gap-3 px-8 pb-7 pt-2">
                            <form method="POST" :action="selected ? selected.reject_url : ''">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="flex items-center gap-1.5 bg-danger text-white font-semibold px-6 py-2.5 rounded-full hover:opacity-90 transition">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Tolak
                                </button>
                            </form>
                            <form method="POST" :action="selected ? selected.approve_url : ''">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="flex items-center gap-1.5 bg-success text-white font-semibold px-6 py-2.5 rounded-full hover:opacity-90 transition">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    Setujui
                                </button>
                            </form>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-admin-layout>