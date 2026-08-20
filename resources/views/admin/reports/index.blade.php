<x-admin-layout title="Laporan">
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-secondary">Laporan</h1>
            <p class="text-slate-500 text-sm mt-0.5">Ringkasan aktivitas peminjaman barang kampus</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.reports.export-pdf', ['range' => $range, 'category' => $category]) }}"
                class="flex items-center gap-2 bg-danger text-white font-semibold px-5 py-2.5 rounded-xl hover:opacity-90 transition text-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 3v4a1 1 0 001 1h4" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 21H7a2 2 0 01-2-2V5a2 2 0 012-2h7l5 5v11a2 2 0 01-2 2z" />
                </svg>
                Export PDF
            </a>

            <a href="{{ route('admin.reports.export', ['range' => $range, 'category' => $category]) }}"
                class="flex items-center gap-2 bg-secondary text-white font-semibold px-5 py-2.5 rounded-xl hover:opacity-90 transition text-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M4 19h16" />
                </svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-surface rounded-2xl shadow-sm p-6 mb-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <label class="flex items-center gap-2 text-sm font-semibold text-secondary mb-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="4" width="18" height="17" rx="2" />
                    <path stroke-linecap="round" d="M16 2v4M8 2v4M3 9h18" />
                </svg>
                Rentang Tanggal
            </label>
            <select name="range" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 bg-background px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                <option value="7d" @selected($range === '7d')>7 Hari Terakhir</option>
                <option value="30d" @selected($range === '30d')>30 Hari Terakhir</option>
                <option value="this_month" @selected($range === 'this_month')>Bulan Ini</option>
                <option value="last_month" @selected($range === 'last_month')>Bulan Lalu</option>
                <option value="all" @selected($range === 'all')>Semua Waktu</option>
            </select>
        </div>

        <div>
            <label class="text-sm font-semibold text-secondary mb-2 block">Filter Kategori</label>
            <select name="category" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 bg-background px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                <option value="all" @selected($category === 'all')>Semua Kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c }}" @selected($category === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- Kartu Ringkasan --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-surface rounded-2xl shadow-sm p-5">
            <p class="text-slate-400 text-xs font-medium mb-1">Peminjaman Terealisasi</p>
            <p class="text-2xl font-bold text-secondary">{{ $summary['total_transactions'] }}</p>
        </div>
        <div class="bg-surface rounded-2xl shadow-sm p-5">
            <p class="text-slate-400 text-xs font-medium mb-1">Total Unit Dipinjam</p>
            <p class="text-2xl font-bold text-secondary">{{ $summary['total_unit'] }}</p>
        </div>
        <div class="bg-surface rounded-2xl shadow-sm p-5">
            <p class="text-slate-400 text-xs font-medium mb-1">Total Peminjam</p>
            <p class="text-2xl font-bold text-secondary">{{ $summary['total_peminjam'] }}</p>
        </div>
        <div class="bg-surface rounded-2xl shadow-sm p-5">
            <p class="text-slate-400 text-xs font-medium mb-1">Rata-rata Durasi</p>
            <p class="text-2xl font-bold text-secondary">{{ $summary['avg_duration'] }} <span
                    class="text-sm font-medium text-slate-400">hari</span></p>
        </div>
        <div class="bg-surface rounded-2xl shadow-sm p-5">
            <p class="text-slate-400 text-xs font-medium mb-1">Total Pendapatan</p>
            <p class="text-2xl font-bold text-success">Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-surface rounded-2xl shadow-sm p-5">
            <p class="text-slate-400 text-xs font-medium mb-1">Denda Belum Dibayar</p>
            <p class="text-2xl font-bold {{ $summary['total_unpaid'] > 0 ? 'text-danger' : 'text-secondary' }}">
                Rp {{ number_format($summary['total_unpaid'], 0, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Breakdown Status --}}
    <div class="bg-surface rounded-2xl shadow-sm p-6 mb-6">
        <h2 class="font-bold text-secondary mb-4">Status Pengajuan pada Rentang Ini</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="flex items-center gap-3 bg-background rounded-xl p-4">
                <span class="w-2.5 h-2.5 rounded-full bg-warning shrink-0"></span>
                <div>
                    <p class="text-lg font-bold text-secondary">{{ $statusBreakdown['pending'] }}</p>
                    <p class="text-xs text-slate-400">Tertunda</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-background rounded-xl p-4">
                <span class="w-2.5 h-2.5 rounded-full bg-success shrink-0"></span>
                <div>
                    <p class="text-lg font-bold text-secondary">{{ $statusBreakdown['booked'] }}</p>
                    <p class="text-xs text-slate-400">Disetujui / Dipinjam</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-background rounded-xl p-4">
                <span class="w-2.5 h-2.5 rounded-full bg-info shrink-0"></span>
                <div>
                    <p class="text-lg font-bold text-secondary">{{ $statusBreakdown['completed'] }}</p>
                    <p class="text-xs text-slate-400">Selesai</p>
                </div>
            </div>
            <div class="flex items-center gap-3 bg-background rounded-xl p-4">
                <span class="w-2.5 h-2.5 rounded-full bg-danger shrink-0"></span>
                <div>
                    <p class="text-lg font-bold text-secondary">{{ $statusBreakdown['rejected'] }}</p>
                    <p class="text-xs text-slate-400">Ditolak</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Laporan Per Barang --}}
        <div class="bg-surface rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h2 class="font-bold text-secondary">Laporan Per Barang</h2>
                <p class="text-slate-400 text-xs mt-0.5">Barang paling banyak dipinjam pada rentang ini</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th class="py-3 px-6 font-semibold">Barang</th>
                            <th class="py-3 px-6 font-semibold">Unit</th>
                            <th class="py-3 px-6 font-semibold">Frekuensi</th>
                            <th class="py-3 px-6 font-semibold">Durasi Rata&sup2;</th>
                            <th class="py-3 px-6 font-semibold">Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($itemRows as $row)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-3.5 px-6 font-medium text-secondary">
                                    {{ $row['name'] }}
                                    <p class="text-xs text-slate-400 font-normal">{{ $row['category'] }}</p>
                                </td>
                                <td class="py-3.5 px-6 text-slate-500">{{ $row['total_unit'] }}</td>
                                <td class="py-3.5 px-6 text-slate-500">{{ $row['frequency'] }}x</td>
                                <td class="py-3.5 px-6 text-slate-500">{{ $row['avg_duration'] }} hari</td>
                                <td class="py-3.5 px-6 text-slate-500">
                                    {{ $row['total_revenue'] > 0 ? 'Rp ' . number_format($row['total_revenue'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-slate-500 py-10">Tidak ada data pada rentang ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Laporan Per Peminjam --}}
        <div class="bg-surface rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h2 class="font-bold text-secondary">Laporan Per Peminjam</h2>
                <p class="text-slate-400 text-xs mt-0.5">Mahasiswa/dosen paling aktif meminjam pada rentang ini</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-secondary text-white">
                        <tr>
                            <th class="py-3 px-6 font-semibold">Nama</th>
                            <th class="py-3 px-6 font-semibold">Pengajuan</th>
                            <th class="py-3 px-6 font-semibold">Unit</th>
                            <th class="py-3 px-6 font-semibold">Denda</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($borrowerRows as $row)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="py-3.5 px-6 font-medium text-secondary">
                                    {{ $row['name'] }}
                                    <p class="text-xs text-slate-400 font-normal">{{ $row['nim_nidn'] ?? '-' }}</p>
                                </td>
                                <td class="py-3.5 px-6 text-slate-500">{{ $row['total_requests'] }}x</td>
                                <td class="py-3.5 px-6 text-slate-500">{{ $row['total_unit'] }}</td>
                                <td class="py-3.5 px-6 text-slate-500">
                                    {{ $row['total_fine'] > 0 ? 'Rp ' . number_format($row['total_fine'], 0, ',', '.') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-slate-500 py-10">Tidak ada data pada rentang ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>