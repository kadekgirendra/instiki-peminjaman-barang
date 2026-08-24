<x-admin-layout title="Detail User">
    <a href="{{ route('admin.users.index') }}"
        class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-secondary mb-4 transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Kelola User
    </a>

    @if (session('success'))
        <div class="bg-success/10 text-success border border-success/20 rounded-xl px-5 py-3 mb-6 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- card user profile --}}
        <div class="bg-surface rounded-2xl shadow-sm p-7">
            <div class="flex items-center gap-4 mb-6">
                <div
                    class="w-14 h-14 rounded-full bg-secondary text-white flex items-center justify-center font-bold text-xl shrink-0">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg font-bold text-secondary truncate">{{ $user->name }}</h1>
                    <span
                        class="{{ $user->role === 'admin' ? 'bg-primary' : 'bg-slate-400' }} text-white text-xs font-semibold px-3 py-1 rounded-full inline-block mt-1">
                        {{ $user->role === 'admin' ? 'Admin' : 'User' }}
                    </span>
                </div>
            </div>

            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-slate-400">Username</dt>
                    <dd class="text-secondary font-medium mt-0.5">{{ $user->username }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">NIM / NIDN</dt>
                    <dd class="text-secondary font-medium mt-0.5">{{ $user->nim_nidn ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-400">Terdaftar sejak</dt>
                    <dd class="text-secondary font-medium mt-0.5">{{ $user->created_at->translatedFormat('j F Y') }}
                    </dd>
                </div>
            </dl>

            <div class="grid grid-cols-3 gap-2 mt-7 pt-6 border-t border-slate-100 text-center">
                <div>
                    <p class="text-xl font-bold text-secondary">{{ $summary['total'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Pengajuan</p>
                </div>
                <div>
                    <p class="text-xl font-bold text-success">{{ $summary['active'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Dipinjam</p>
                </div>
                <div>
                    <p class="text-xl font-bold text-info">{{ $summary['completed'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Selesai</p>
                </div>
            </div>

            <a href="{{ route('admin.users.edit', $user) }}"
                class="block text-center mt-7 px-6 py-3 rounded-full border border-slate-200 text-secondary font-semibold text-sm hover:bg-slate-50 transition">
                Edit User
            </a>
        </div>

        {{-- Riwayat transaksi --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="bg-surface rounded-2xl shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100">
                    <h2 class="font-bold text-secondary">Riwayat Peminjaman</h2>
                    <p class="text-slate-400 text-xs mt-0.5">Rincian barang & jumlah unit tiap pengajuan</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($transactions as $trx)
                        <div class="px-6 py-5">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div>
                                    <p class="text-xs text-slate-400">
                                        {{ $trx['tanggal_pinjam'] }} &ndash; {{ $trx['tanggal_kembali'] }}
                                    </p>
                                    <p class="text-sm text-slate-500 mt-0.5">
                                        {{ $trx['jumlah_jenis'] }} jenis barang &middot; {{ $trx['total_unit'] }} unit total
                                    </p>
                                </div>
                                <span
                                    class="{{ $trx['status_badge'] }} text-xs font-semibold px-3 py-1 rounded-full shrink-0">
                                    {{ $trx['status_label'] }}
                                </span>
                            </div>

                            {{-- Rincian tiap barang dalam pengajuan ini --}}
                            <div class="bg-background rounded-xl overflow-hidden">
                                @foreach ($trx['items_detail'] as $item)
                                    <div
                                        class="flex items-center justify-between px-4 py-2.5 {{ !$loop->last ? 'border-b border-white' : '' }}">
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-secondary truncate">{{ $item['name'] }}</p>
                                            <p class="text-xs text-slate-400">{{ $item['category'] }}</p>
                                        </div>
                                        <div class="flex items-center gap-4 shrink-0">
                                            <span class="text-sm font-semibold text-secondary">{{ $item['quantity'] }}
                                                unit</span>
                                            @if ($item['fine'] > 0)
                                                <span class="text-xs text-danger font-medium">
                                                    Denda Rp {{ number_format($item['fine'], 0, ',', '.') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if ($trx['total_fine'] > 0)
                                <div class="flex items-center justify-between mt-2">
                                    <p class="text-xs text-danger font-medium">
                                        Total denda pengajuan ini: Rp {{ number_format($trx['total_fine'], 0, ',', '.') }}
                                    </p>
                                    <span
                                        class="{{ $trx['is_paid'] ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }} text-xs font-semibold px-2.5 py-1 rounded-full shrink-0">
                                        {{ $trx['is_paid'] ? 'Lunas' : 'Belum Dibayar' }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-slate-500 py-10">User ini belum pernah meminjam barang.</p>
                    @endforelse
                </div>
            </div>

            {{-- Rekap barang yang paling sering dipinjam user ini --}}
            @if ($itemSummary->isNotEmpty())
                <div class="bg-surface rounded-2xl shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h2 class="font-bold text-secondary">Rekap Barang</h2>
                        <p class="text-slate-400 text-xs mt-0.5">Total barang yang pernah dipinjam, sepanjang riwayat</p>
                    </div>

                    <table class="w-full text-sm text-left">
                        <thead class="bg-background text-slate-500">
                            <tr>
                                <th class="py-3 px-6 font-semibold">Barang</th>
                                <th class="py-3 px-6 font-semibold">Kategori</th>
                                <th class="py-3 px-6 font-semibold">Total Unit Dipinjam</th>
                                <th class="py-3 px-6 font-semibold">Frekuensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($itemSummary as $item)
                                <tr class="border-b border-slate-100 last:border-0">
                                    <td class="py-3 px-6 font-medium text-secondary">{{ $item['name'] }}</td>
                                    <td class="py-3 px-6 text-slate-500">{{ $item['category'] }}</td>
                                    <td class="py-3 px-6 text-slate-500">{{ $item['total_unit'] }} unit</td>
                                    <td class="py-3 px-6 text-slate-500">{{ $item['total_pinjam'] }}x dipinjam</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
