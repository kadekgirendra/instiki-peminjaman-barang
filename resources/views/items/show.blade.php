<x-app-layout>
    <h1 class="text-2xl font-bold text-secondary mb-6">Detail Barang</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        {{-- Gambar barang --}}
        <div class="bg-surface rounded-2xl shadow-sm p-6 flex items-center justify-center h-105">
            @if ($item->image)
                <img src="{{ asset('storage/' . $item->image) }}" class="max-h-full max-w-full object-contain">
            @else
                <span class="text-slate-400">Tidak ada foto</span>
            @endif
        </div>

        {{-- Info barang --}}
        <div x-data="{ showQtyModal: false, quantity: 1, maxStock: {{ $availableStock }} }">
            <span class="inline-block bg-accent-bg text-accent text-sm font-semibold px-4 py-1.5 rounded-full mb-4">
                {{ $item->category }}
            </span>

            <h2 class="text-4xl font-bold text-secondary mb-6">{{ $item->name }}</h2>

            <div class="bg-surface rounded-2xl shadow-sm p-6 mb-6">
                <p class="text-slate-500 text-sm mb-2">Ketersediaan Stok</p>
                <p class="mb-4">
                    <span class="text-4xl font-bold text-secondary">{{ $availableStock }}</span>
                    <span class="text-slate-500 ml-1">unit tersedia</span>
                </p>
                <div class="flex gap-2">
                    @php $filledSegments = min($availableStock, 5); @endphp
                    @for ($i = 1; $i <= 5; $i++)
                        <span class="h-2 flex-1 rounded-full {{ $i <= $filledSegments ? 'bg-accent' : 'bg-slate-200' }}"></span>
                    @endfor
                </div>
            </div>

            <div class="bg-surface rounded-2xl shadow-sm p-6 mb-6">
                <h3 class="font-semibold text-secondary text-lg mb-3">Deskripsi</h3>
                <p class="text-slate-600 leading-relaxed">{{ $item->description }}</p>
            </div>

            @if ($availableStock > 0)
                <button type="button" @click="showQtyModal = true; quantity = 1"
                        class="block w-full text-center bg-primary text-white font-semibold py-3.5 rounded-xl hover:opacity-90 transition mb-4">
                    Pinjam Sekarang
                </button>
            @else
                <button disabled
                        class="w-full bg-slate-200 text-slate-400 font-semibold py-3.5 rounded-xl cursor-not-allowed mb-4">
                    Stok Habis
                </button>
            @endif

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex gap-3">
                <svg class="w-5 h-5 text-slate-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="9"/>
                    <path stroke-linecap="round" d="M12 8h.01M11 12h1v4h1"/>
                </svg>
                <p class="text-sm text-slate-500">
                    Pastikan kamu membawa KTM yang masih berlaku untuk melengkapi proses peminjaman.
                </p>
            </div>

            {{-- Modal Jumlah Barang --}}
            <div x-show="showQtyModal" x-cloak
                 class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-6">
                <div class="bg-white rounded-2xl p-8 w-full max-w-sm" @click.outside="showQtyModal = false">
                    <h2 class="text-2xl font-bold text-secondary mb-6">Jumlah Barang</h2>

                    <div class="bg-slate-50 rounded-xl p-5 mb-6">
                        <label class="block font-semibold text-secondary mb-2">Jumlah *</label>
                        <div class="flex items-center gap-3">
                            <button type="button" @click="quantity = Math.max(1, quantity - 1)"
                                    class="w-11 h-11 rounded-lg bg-slate-200 text-secondary font-bold text-lg">−</button>
                            <div class="flex-1 text-center bg-white border border-slate-200 rounded-lg py-2.5 font-semibold text-lg">
                                <span x-text="quantity"></span>
                            </div>
                            <button type="button" @click="quantity = Math.min(maxStock, quantity + 1)"
                                    class="w-11 h-11 rounded-lg bg-primary text-white font-bold text-lg">+</button>
                        </div>
                    </div>

                    <p class="text-center text-secondary font-medium mb-6">
                        Total barang yang dipinjam: <span x-text="quantity"></span> unit
                    </p>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="showQtyModal = false"
                                class="bg-slate-100 text-secondary font-semibold py-3 rounded-xl">
                            Batal
                        </button>

                        <form method="POST" action="{{ route('loan-cart.add') }}">
                            @csrf
                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                            <input type="hidden" name="quantity" :value="quantity">
                            <button type="submit" class="w-full bg-primary text-white font-semibold py-3 rounded-xl">
                                Selesai
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>