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
                <p class="text-slate-500 text-sm mt-0.5">Jadwal barang yang sedang dipinjam dan harus kembali</p>
            </div>

            <span class="bg-surface shadow-sm text-secondary text-sm font-semibold px-4 py-2 rounded-full">
                {{ $totalBooked }} barang sedang dipinjam
            </span>
        </div>

        {{-- FullCalendar target --}}
        <div class="bg-surface rounded-2xl shadow-sm p-5">
            <div id="sirkulasi-calendar" data-events='@json($events)'></div>
        </div>

        <div class="flex items-center gap-2 mt-4 text-xs text-slate-500">
            <span class="w-3 h-3 rounded-sm bg-warning inline-block"></span>
            Barang jatuh tempo kembali pada tanggal tersebut &mdash; klik tanggal/label untuk detail
        </div>

        {{-- Modal detail hari --}}
        <div x-show="selectedDay" x-cloak @click.self="selectedDay = null" @keydown.escape.window="selectedDay = null"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-6">
            <div class="bg-white rounded-3xl w-full max-w-lg shadow-xl max-h-[85vh] overflow-y-auto" @click.stop>
                <template x-if="selectedDay">
                    <div>
                        <div class="flex items-center justify-between px-7 pt-6 pb-4 border-b border-slate-100">
                            <div>
                                <h3 class="text-lg font-bold text-secondary">Jatuh Tempo</h3>
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
                                <div class="flex items-center justify-between bg-background rounded-xl px-4 py-3">
                                    <div>
                                        <p class="font-medium text-secondary text-sm" x-text="item.item_name"></p>
                                        <p class="text-xs text-slate-500"
                                            x-text="item.user_name + ' \u2022 ' + item.user_nim"></p>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-500"
                                        x-text="item.quantity + ' unit'"></span>
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