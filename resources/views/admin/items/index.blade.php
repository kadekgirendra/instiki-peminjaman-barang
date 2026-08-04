<x-admin-layout title="Manajemen Inventaris">
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-secondary">Manajemen Inventaris</h1>
            <p class="text-slate-500 text-sm mt-0.5">Kelola peralatan dan perangkat kampus</p>
        </div>

        <div class="flex items-center gap-3">
            <form method="GET">
                @if (request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif
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

            <a href="{{ route('admin.items.create') }}"
                class="flex items-center gap-1.5 bg-primary text-white font-semibold px-5 py-2.5 rounded-full hover:opacity-90 transition text-sm">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                </svg>
                Tambah
            </a>
        </div>
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
                    <th class="py-4 px-6 font-semibold">Barang</th>
                    <th class="py-4 px-6 font-semibold">Kategori</th>
                    <th class="py-4 px-6 font-semibold">Stok Total</th>
                    <th class="py-4 px-6 font-semibold">Stok Tersedia</th>
                    <th class="py-4 px-6 font-semibold">Status</th>
                    <th class="py-4 px-6 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php
                        $status = $item->available_stock === 0
                            ? ['label' => 'Stok Habis', 'class' => 'bg-danger']
                            : ($item->available_stock <= 3
                                ? ['label' => 'Stok Sedikit', 'class' => 'bg-warning']
                                : ['label' => 'Tersedia', 'class' => 'bg-success']);
                    @endphp

                    <tr x-data="{ confirmDelete: false }" class="border-b border-slate-100 last:border-0">
                        <td class="py-4 px-6 font-medium text-secondary">
                            <div class="flex items-center gap-3">
                                @if ($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                        class="w-10 h-10 rounded-lg object-cover shrink-0 border border-slate-100">
                                @else
                                    <div
                                        class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 text-slate-400">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2">
                                            <rect x="3" y="3" width="18" height="18" rx="2" />
                                            <circle cx="8.5" cy="8.5" r="1.5" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21" />
                                        </svg>
                                    </div>
                                @endif
                                <span>{{ $item->name }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-slate-500">{{ $item->category }}</td>
                        <td class="py-4 px-6 text-slate-500">{{ $item->total_stock }} unit</td>
                        <td class="py-4 px-6 text-slate-500">
                            {{ $item->available_stock }} unit
                            @if ($item->borrowed_now > 0)
                                <span class="text-xs text-slate-400">({{ $item->borrowed_now }} dipinjam)</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span
                                class="{{ $status['class'] }} text-white text-xs font-semibold px-3.5 py-1.5 rounded-full">
                                {{ $status['label'] }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.items.edit', $item) }}"
                                    class="text-secondary hover:text-primary transition">
                                    <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2v-5" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                </a>

                                <button @click="confirmDelete = true" type="button"
                                    class="text-secondary hover:text-danger transition">
                                    <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z" />
                                        <path stroke-linecap="round" d="M10 11v6M14 11v6" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Modal konfirmasi hapus --}}
                            <div x-show="confirmDelete" x-cloak @click.outside="confirmDelete = false"
                                class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-6">
                                <div class="bg-white rounded-3xl p-8 w-full max-w-sm text-center shadow-xl">
                                    <div
                                        class="w-16 h-16 bg-danger/10 rounded-full flex items-center justify-center mx-auto mb-5">
                                        <svg class="w-7 h-7 text-danger" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z" />
                                            <path stroke-linecap="round" d="M10 11v6M14 11v6" />
                                        </svg>
                                    </div>

                                    <h3 class="text-xl font-bold text-secondary mb-2">Hapus Barang</h3>
                                    <p class="text-slate-500 mb-7">Apakah anda yakin ingin menghapus barang ini?</p>

                                    <div class="flex gap-3">
                                        <button type="button" @click="confirmDelete = false"
                                            class="flex-1 border border-slate-200 text-secondary font-semibold py-3 rounded-full hover:bg-slate-50 transition">
                                            Batal
                                        </button>
                                        <form method="POST" action="{{ route('admin.items.destroy', $item) }}"
                                            class="flex-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="w-full bg-primary text-white font-semibold py-3 rounded-full hover:opacity-90 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 py-10">Belum ada barang. Tambahkan barang pertama.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <div class="mt-5">
            {{ $items->links() }}
        </div>
    @endif
</x-admin-layout>