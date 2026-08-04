<x-admin-layout title="Laporan">
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-secondary">Laporan</h1>
            <p class="text-slate-500 text-sm mt-0.5">Cek Laporan dan cetak Laporan</p>
        </div>

        <a href="{{ route('admin.reports.export', ['range' => $range, 'category' => $category]) }}"
            class="flex items-center gap-2 bg-secondary text-white font-semibold px-5 py-2.5 rounded-xl hover:opacity-90 transition text-sm">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M4 19h16" />
            </svg>
            Export Laporan
        </a>
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
                <option value="all" @selected($category === 'all')>All Categories</option>
                @foreach ($categories as $c)
                    <option value="{{ $c }}" @selected($category === $c)>{{ $c }}</option>
                @endforeach
            </select>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-surface rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full text-sm text-left">
            <thead class="bg-secondary text-white">
                <tr>
                    <th class="py-4 px-6 font-semibold">Barang</th>
                    <th class="py-4 px-6 font-semibold">Total Dipinjam</th>
                    <th class="py-4 px-6 font-semibold">Durasi Rata-rata</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="py-4 px-6 font-medium text-secondary">{{ $row['name'] }}</td>
                        <td class="py-4 px-6 text-slate-500">{{ $row['total'] }}</td>
                        <td class="py-4 px-6 text-slate-500">{{ $row['avg_duration'] }} Hari</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-slate-500 py-10">Tidak ada data peminjaman pada rentang ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>