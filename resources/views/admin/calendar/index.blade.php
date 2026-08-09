@push('styles')
    <style>
        #sirkulasi-calendar .fc-toolbar-title {
            color: var(--color-secondary);
            font-size: 1.125rem;
            font-weight: 700;
        }

        #sirkulasi-calendar .fc-button {
            background: var(--color-secondary) !important;
            border: none !important;
            box-shadow: none !important;
        }

        #sirkulasi-calendar .fc-button:hover {
            opacity: .9;
        }

        #sirkulasi-calendar .fc-button-active {
            background: var(--color-primary) !important;
        }

        #sirkulasi-calendar .fc-daygrid-day-number {
            color: #64748b;
            font-size: .8rem;
        }

        #sirkulasi-calendar .fc-day-today {
            background: color-mix(in srgb, var(--color-primary) 6%, white) !important;
        }

        #sirkulasi-calendar .fc-event {
            cursor: pointer;
            border-radius: 6px;
            font-size: .7rem;
            padding: 1px 4px;
        }

        #sirkulasi-calendar th {
            text-transform: capitalize;
            color: var(--color-secondary);
            font-size: .75rem;
            padding: 8px 0;
        }

        #sirkulasi-calendar,
        #sirkulasi-calendar .fc {
            font-family: inherit;
        }
    </style>
@endpush

<x-admin-layout title="Kalender Sirkulasi">
    <div x-data="{
            selectedDay: null,
        }" x-init="
            window.addEventListener('open-day-detail', (e) => { selectedDay = e.detail });
        ">

        <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-secondary">Kalender Sirkulasi Master</h1>
                <p class="text-slate-500 text-sm mt-0.5">Jadwal barang mulai dipinjam dan harus kembali</p>
            </div>
        </div>

        {{-- Kartu Ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-surface rounded-2xl shadow-sm p-5">
                <p class="text-slate-400 text-xs font-medium mb-1">Sedang Dipinjam</p>
                <p class="text-2xl font-bold text-secondary">{{ $summary['total_booked'] }}</p>
            </div>
            <div class="bg-surface rounded-2xl shadow-sm p-5">
                <p class="text-slate-400 text-xs font-medium mb-1">Mulai Hari Ini</p>
                <p class="text-2xl font-bold text-info">{{ $summary['starting_today'] }}</p>
            </div>
            <div class="bg-surface rounded-2xl shadow-sm p-5">
                <p class="text-slate-400 text-xs font-medium mb-1">Jatuh Tempo Hari Ini</p>
                <p class="text-2xl font-bold text-warning">{{ $summary['due_today'] }}</p>
            </div>
            <div class="bg-surface rounded-2xl shadow-sm p-5">
                <p class="text-slate-400 text-xs font-medium mb-1">Terlambat Dikembalikan</p>
                <p class="text-2xl font-bold text-danger">{{ $summary['overdue'] }}</p>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" class="bg-surface rounded-2xl shadow-sm p-5 mb-6 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px]">
                <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7" />
                    <path stroke-linecap="round" d="M21 21l-3.5-3.5" />
                </svg>
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Cari barang, nama, atau NIM/NIDN peminjam..."
                    class="pl-10 pr-4 py-2.5 w-full rounded-full border-0 bg-background text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>

            <select name="category" onchange="this.form.submit()"
                class="px-4 py-2.5 rounded-full border-0 bg-background text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
                <option value="all" @selected($category === 'all')>Semua Kategori</option>
                @foreach ($categories as $c)
                    <option value="{{ $c }}" @selected($category === $c)>{{ $c }}</option>
                @endforeach
            </select>

            <button type="submit"
                class="bg-primary text-white font-semibold px-6 py-2.5 rounded-full hover:opacity-90 transition text-sm">
                Terapkan
            </button>

            @if ($search || $category !== 'all')
                <a href="{{ route('admin.calendar') }}"
                    class="text-sm text-slate-500 hover:text-secondary transition">Reset</a>
            @endif
        </form>

        {{-- FullCalendar target --}}
        <div class="bg-surface rounded-2xl shadow-sm p-5">
            <div id="sirkulasi-calendar" data-events='@json($events)'></div>
        </div>

        {{-- Legenda --}}
        <div class="flex items-center flex-wrap gap-5 mt-4 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-info inline-block"></span>
                Mulai Pinjam
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-warning inline-block"></span>
                Jatuh Tempo
            </span>
            <span class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-sm bg-danger inline-block"></span>
                Terlambat
            </span>
            <span>&mdash; klik tanggal atau label untuk lihat detail</span>
        </div>

        {{-- Modal detail hari --}}
        <div x-show="selectedDay" x-cloak @click.self="selectedDay = null" @keydown.escape.window="selectedDay = null"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-6">
            <div class="bg-white rounded-3xl w-full max-w-lg shadow-xl max-h-[85vh] overflow-y-auto" @click.stop>
                <template x-if="selectedDay">
                    <div>
                        <div class="flex items-center justify-between px-7 pt-6 pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-lg font-bold text-secondary">Detail Hari</h3>
                                <p class="text-sm text-slate-500" x-text="selectedDay.date"></p>
                            </div>
                            <button @click="selectedDay = null" type="button"
                                class="text-slate-400 hover:text-secondary transition">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="px-7 py-5 space-y-3">
                            <template x-for="(item, idx) in selectedDay.items" :key="idx">
                                <div class="bg-background rounded-xl px-4 py-3.5">
                                    <div class="flex items-start justify-between gap-3 mb-1.5">
                                        <div class="min-w-0">
                                            <p class="font-medium text-secondary text-sm" x-text="item.item_name"></p>
                                            <p class="text-xs text-slate-400" x-text="item.category"></p>
                                        </div>
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0" :class="{
                                                'bg-info/10 text-info': item.type === 'start',
                                                'bg-warning/10 text-warning': item.type === 'due',
                                                'bg-danger/10 text-danger': item.type === 'overdue',
                                            }" x-text="item.label"></span>
                                    </div>
                                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-white">
                                        <p class="text-xs text-slate-500"
                                            x-text="item.user_name + ' \u2022 ' + (item.user_nim || '-')"></p>
                                        <span class="text-xs font-semibold text-slate-600"
                                            x-text="item.quantity + ' unit'"></span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-1.5" x-show="item.purpose"
                                        x-text="'Keperluan: ' + item.purpose"></p>
                                </div>
                            </template>
                        </div>

                        <div class="px-7 pb-6">
                            <a href="{{ route('admin.transactions.index') }}"
                                class="block text-center bg-secondary text-white font-semibold py-2.5 rounded-full hover:opacity-90 transition text-sm">
                                Kelola di Halaman Permintaan
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    @vite('resources/js/admin-calendar.js')
</x-admin-layout>