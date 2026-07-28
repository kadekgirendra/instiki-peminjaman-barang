<x-app-layout>
    <form method="GET">
        {{-- Baris atas: judul kiri, search + kategori kanan (sesuai UI asli) --}}
        <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
            <h1 class="text-2xl font-bold text-secondary">Katalog Barang</h1>

            <div class="flex items-center gap-3">
                <div class="relative">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"/>
                        <path stroke-linecap="round" d="M21 21l-3.5-3.5"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items..."
                           class="pl-10 pr-4 py-2.5 w-64 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <select name="category"
                        class="px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-600 focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="all">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') === $category)>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Baris filter tanggal: kiri, sesuai permintaan --}}
        <div class="flex flex-wrap items-end gap-3 mb-8 bg-surface p-4 rounded-xl shadow-sm">
            <div class="w-64">
                <label class="block text-sm font-semibold text-secondary mb-1">Tanggal Pinjam</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <div class="w-64">
                <label class="block text-sm font-semibold text-secondary mb-1">Tanggal Kembali</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            {{-- 1 slot tombol — otomatis berubah wujud tergantung ada filter aktif atau tidak --}}
            @if (request()->hasAny(['search', 'category', 'start_date', 'end_date']))
                <a href="{{ route('items.index') }}"
                   class="flex items-center gap-1.5 justify-center bg-slate-100 text-slate-600 px-6 py-2.5 rounded-lg font-semibold h-10.5 hover:bg-slate-200 transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Reset
                </a>
            @else
                <button type="submit"
                        class="bg-primary text-white px-6 py-2.5 rounded-lg font-semibold h-10.5">
                    Cari
                </button>
            @endif
        </div>
    </form>

    {{-- Grid barang --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($items as $item)
            @php $isAvailable = $item->available_stock > 0; @endphp

            <div class="bg-surface rounded-xl shadow-sm p-5">
                <div class="h-44 bg-slate-100 rounded-lg mb-4 flex items-center justify-center overflow-hidden {{ !$isAvailable ? 'opacity-50' : '' }}">
                    @if ($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" class="h-full w-full object-contain">
                    @else
                        <span class="text-slate-400 text-sm">Tidak ada foto</span>
                    @endif
                </div>

                <span class="inline-block bg-accent-bg text-accent text-xs font-semibold px-3 py-1 rounded-full mb-3">
                    {{ $item->category }}
                </span>

                <h3 class="font-semibold text-lg {{ $isAvailable ? 'text-secondary' : 'text-slate-400' }} mb-1">
                    {{ $item->name }}
                </h3>

                <p class="text-sm {{ $isAvailable ? 'text-slate-500' : 'text-slate-300' }} mb-4 line-clamp-2">
                    {{ $item->description }}
                </p>

                <p class="text-sm mb-4">
                    Stock:
                    <span class="font-semibold {{ $isAvailable ? 'text-success' : 'text-danger' }}">
                        {{ $item->available_stock }} units
                    </span>
                </p>

                @if ($isAvailable)
                    <a href="{{ route('items.show', $item) }}"
                       class="block text-center bg-primary text-white font-semibold py-2.5 rounded-lg hover:opacity-90 transition">
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
            <p class="text-slate-500 col-span-3 text-center py-10">
                Tidak ada barang ditemukan.
            </p>
        @endforelse
    </div>
</x-app-layout>