<x-app-layout>
    <div
        x-data="{ filterOpen: false, category: '{{ request('category', 'all') }}', dateStart: '{{ request('start_date') }}', dateEnd: '{{ request('end_date') }}' }">
        <form method="GET">
            <input type="hidden" name="category" x-bind:value="category">
            <input type="hidden" name="start_date" x-bind:value="dateStart">
            <input type="hidden" name="end_date" x-bind:value="dateEnd">
            {{-- Judul --}}
            <h1 class="text-2xl font-bold text-secondary mb-4 lg:mb-0 lg:inline">Katalog Barang</h1>

            {{-- baris search + filter (desktop) --}}
            <div class="flex items-center gap-3 mb-4 lg:mb-6 lg:float-right lg:-mt-1">
                <div class="relative flex-1 lg:flex-none lg:w-64">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <path stroke-linecap="round" d="M21 21l-3.5-3.5" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari barang..."
                        class="pl-10 pr-4 py-2.5 w-full rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                {{-- Dropdown kategori — cuma tampil inline di desktop --}}
                <select x-model="category" @change="$el.closest('form').submit()" aria-label="Filter kategori"
                    class="hidden lg:block w-auto px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-600 focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="all">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>

                {{-- Tombol filter — cuma tampil di mobile --}}
                <button type="button" @click="filterOpen = !filterOpen"
                    class="lg:hidden relative shrink-0 w-11 h-11 flex items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-secondary">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18M6 8h12M9 12h6M11 16h2" />
                    </svg>
                    @if (request()->hasAny(['category', 'start_date', 'end_date']))
                        <span class="absolute -top-1 -right-1 w-3 h-3 bg-primary rounded-full border-2 border-white"></span>
                    @endif
                </button>
            </div>
            <div class="clear-both"></div>

            {{-- ============ PANEL FILTER DESKTOP — statis, TIDAK pakai Alpine sama sekali ============ --}}
            <div class="hidden lg:flex flex-row flex-wrap items-end gap-3 mb-6 bg-surface p-4 rounded-xl shadow-sm">
                <div class="w-64">
                    <label for="start-date-desktop" class="block text-sm font-semibold text-secondary mb-1">Tanggal
                        Pinjam</label>
                    <input type="date" id="start-date-desktop" x-model="dateStart"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div class="w-64">
                    <label for="end-date-desktop" class="block text-sm font-semibold text-secondary mb-1">Tanggal
                        Kembali</label>
                    <input type="date" id="end-date-desktop" x-model="dateEnd"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                @if (request()->hasAny(['search', 'category', 'start_date', 'end_date']))
                    <a href="{{ route('items.index') }}"
                        class="flex items-center gap-1.5 justify-center bg-slate-100 text-slate-600 px-6 py-2.5 rounded-lg font-semibold h-10.5 hover:bg-slate-200 transition">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset
                    </a>
                @else
                    <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-lg font-semibold h-10.5">
                        Cari
                    </button>
                @endif
            </div>

            {{-- ============ PANEL FILTER MOBILE — collapsible, terpisah total dari versi desktop ============ --}}
            <div x-show="filterOpen" x-cloak x-collapse
                class="lg:hidden flex flex-col gap-3 mb-6 bg-surface p-4 rounded-xl shadow-sm">

                <div class="w-full">
                    <label for="category-mobile"
                        class="block text-sm font-semibold text-secondary mb-1">Kategori</label>
                    <select id="category-mobile" x-model="category" @change="$el.closest('form').submit()"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="all">Semua Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full">
                    <label for="start-date-mobile" class="block text-sm font-semibold text-secondary mb-1">Tanggal
                        Pinjam</label>
                    <input type="date" id="start-date-mobile" x-model="dateStart"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div class="w-full">
                    <label for="end-date-mobile" class="block text-sm font-semibold text-secondary mb-1">Tanggal
                        Kembali</label>
                    <input type="date" id="end-date-mobile" x-model="dateEnd"
                        class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                @if (request()->hasAny(['search', 'category', 'start_date', 'end_date']))
                    <a href="{{ route('items.index') }}"
                        class="flex items-center gap-1.5 justify-center bg-slate-100 text-slate-600 px-6 py-2.5 rounded-lg font-semibold h-10.5 hover:bg-slate-200 transition w-full">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Reset
                    </a>
                @else
                    <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-lg font-semibold h-10.5 w-full">
                        Cari
                    </button>
                @endif
            </div>
        </form>
    </div>

    {{-- Grid barang --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($items as $item)
            @php $isAvailable = $item->available_stock > 0; @endphp

            <div
                class="bg-surface rounded-xl shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                <div
                    class="h-44 bg-slate-100 rounded-lg mb-4 flex items-center justify-center overflow-hidden {{ !$isAvailable ? 'opacity-50' : '' }}">
                    @if ($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" loading="lazy"
                            class="h-full w-full object-contain">
                    @else
                        <div class="flex flex-col items-center justify-center gap-2">
                            <svg class="w-10 h-10 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M2.25 19.5h19.5M3.75 4.5h16.5a1.5 1.5 0 011.5 1.5v12a1.5 1.5 0 01-1.5 1.5H3.75A1.5 1.5 0 012.25 18V6a1.5 1.5 0 011.5-1.5z" />
                            </svg>
                            <span class="text-slate-400 text-xs">Tidak ada foto</span>
                        </div>
                    @endif
                </div>

                <span class="inline-block bg-category-bg text-category text-xs font-semibold px-3 py-1 rounded-full mb-3">
                    {{ $item->category }}
                </span>

                <h3 class="font-semibold text-lg {{ $isAvailable ? 'text-secondary' : 'text-slate-400' }} mb-1">
                    {{ $item->name }}
                </h3>

                <p class="text-sm {{ $isAvailable ? 'text-slate-500' : 'text-slate-300' }} mb-4 line-clamp-2">
                    {{ $item->description }}
                </p>

                <p class="text-sm mb-4">
                    Stok:
                    <span class="font-semibold {{ $isAvailable ? 'text-success' : 'text-danger' }}">
                        {{ $item->available_stock }} unit
                    </span>
                </p>

                @if ($isAvailable)
                    <a href="{{ route('items.show', $item) }}?start_date={{ request('start_date') }}&end_date={{ request('end_date') }}"
                        class="block text-center bg-primary text-white font-semibold py-2.5 rounded-lg hover:bg-primary-dark transition cursor-pointer">
                        Pinjam
                    </a>
                @else
                    <button disabled
                        class="w-full bg-slate-200 text-slate-400 font-semibold py-2.5 rounded-lg cursor-not-allowed">
                        Pinjam
                    </button>
                @endif
            </div>
        @empty
            <div class="col-span-1 sm:col-span-2 lg:col-span-3 flex flex-col items-center justify-center py-16 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803a7.5 7.5 0 0010.607 0z" />
                    </svg>
                </div>

                @if (request()->hasAny(['search', 'category', 'start_date', 'end_date']))
                    <p class="font-semibold text-secondary mb-1">Barang tidak ditemukan</p>
                    <p class="text-sm text-slate-400 mb-4">Coba ubah filter atau kata kunci pencarian.</p>
                    <a href="{{ route('items.index') }}" class="text-sm font-semibold text-primary hover:underline">Reset pencarian</a>
                @else
                    <p class="font-semibold text-secondary mb-1">Belum ada barang</p>
                    <p class="text-sm text-slate-400">Katalog masih kosong. Barang yang tersedia akan muncul di sini.</p>
                @endif
            </div>
        @endforelse
    </div>
    </div>

    <div class="mt-6">
        {{ $items->links() }}
    </div>
</x-app-layout>
