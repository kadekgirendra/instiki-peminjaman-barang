<x-app-layout>
    <div x-data="{
            showCatalogModal: false,
            showQtyModal: false,
            selectedItem: null,
            quantity: 1,
            search: '',
            category: 'all',
            catalogItems: {{ Illuminate\Support\Js::from($catalogItems->map(fn($i) => [
    'id' => $i->id,
    'name' => $i->name,
    'category' => $i->category,
    'stock' => $i->available_stock,
    'image' => $i->image,
])) }},
            get filteredItems() {
                return this.catalogItems.filter(i =>
                    (this.category === 'all' || i.category === this.category) &&
                    i.name.toLowerCase().includes(this.search.toLowerCase())
                );
            },
            openQtyModal(item) {
                this.selectedItem = item;
                this.quantity = 1;
                this.showCatalogModal = false;
                this.showQtyModal = true;
            }
         }">

        <h1 class="text-2xl font-bold text-secondary mb-6">Pinjam Barang</h1>

        @if ($errors->any())
            <div class="mb-4 bg-danger/10 border border-danger/30 text-danger text-sm px-4 py-3 rounded-lg">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Form utama --}}
            <div class="lg:col-span-2 bg-surface rounded-2xl shadow-sm p-6">
                <form method="POST" action="{{ route('loan-requests.store') }}" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-secondary mb-1">Tanggal Mulai *</label>
                            <input type="date" name="start_date" value="{{ old('start_date') }}" required
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block font-semibold text-secondary mb-1">Tanggal Kembali *</label>
                            <input type="date" name="end_date" value="{{ old('end_date') }}" required
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-secondary mb-1">Catatan (Optional)</label>
                        <textarea name="purpose" rows="4" placeholder="Tambahkan informasi tambahan..."
                            class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">{{ old('purpose') }}</textarea>
                    </div>

                    <div x-data="{ fileName: null, fileSize: null, isImage: false }">
                        <label class="block font-semibold text-secondary mb-1">Dokumen Pendukung *</label>

                        <label x-show="!fileName" @dragover.prevent @dragleave.prevent @drop.prevent="
            $refs.documentInput.files = $event.dataTransfer.files;
            const f = $event.dataTransfer.files[0];
            fileName = f?.name;
            fileSize = f?.size;
            isImage = f?.type.startsWith('image/');
        " class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 rounded-xl py-12 cursor-pointer hover:bg-slate-50 transition">

                            <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-secondary" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                                </svg>
                            </div>

                            <p class="text-secondary font-medium">Drag & drop your file here, or click to browse</p>
                            <p class="text-slate-400 text-sm mt-1">PDF, JPG, PNG (max 5MB)</p>

                            <input type="file" name="document" required class="hidden" accept=".pdf,.jpg,.jpeg,.png"
                                x-ref="documentInput" @change="
                   const f = $event.target.files[0];
                   fileName = f?.name;
                   fileSize = f?.size;
                   isImage = f?.type.startsWith('image/');
               ">
                        </label>

                        {{-- Preview file yang sudah dipilih --}}
                        <div x-show="fileName" x-cloak
                            class="flex items-center gap-4 border-2 border-success/30 bg-success/5 rounded-xl p-4">

                            {{-- Ikon PDF --}}
                            <div x-show="!isImage"
                                class="w-12 h-12 bg-danger/10 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-danger" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6" />
                                </svg>
                            </div>

                            {{-- Ikon Gambar --}}
                            <div x-show="isImage"
                                class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21" />
                                </svg>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-secondary truncate" x-text="fileName"></p>
                                <p class="text-sm text-slate-500"
                                    x-text="fileSize ? (fileSize / 1024).toFixed(0) + ' KB' : ''"></p>
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <svg class="w-5 h-5 text-success" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>

                                <button type="button"
                                    @click="fileName = null; fileSize = null; $refs.documentInput.value = ''"
                                    class="text-slate-400 hover:text-danger">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full bg-primary text-white font-semibold py-3.5 rounded-xl hover:opacity-90 transition">
                        Kirim Permintaan
                    </button>
                </form>
            </div>

            {{-- Sidebar kanan --}}
            <div class="space-y-6">
                <button type="button" @click="showCatalogModal = true"
                    class="w-full flex items-center justify-center gap-2 border-2 border-dashed border-primary text-primary font-semibold py-4 rounded-xl hover:bg-primary/5 transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Item
                </button>

                @foreach ($cartItems as $cartItem)
                    <div class="bg-surface rounded-2xl shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-secondary">Ringkasan Barang</h3>
                            <form method="POST" action="{{ route('loan-cart.remove', $cartItem) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-danger">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                        </div>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Nama Barang :</span>
                                <span class="font-medium text-secondary">{{ Str::limit($cartItem->name, 20) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Kategori:</span>
                                <span class="font-medium text-secondary">{{ $cartItem->category }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Jumlah:</span>
                                <span class="font-medium text-secondary">{{ $cartItem->cart_quantity }} unit</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">Status:</span>
                                <span class="bg-success/10 text-success text-xs font-semibold px-3 py-1 rounded-full">
                                    Tersedia
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Modal: Tambah Barang (Katalog) --}}
        <div x-show="showCatalogModal" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-40 p-6">
            <div class="bg-white rounded-2xl w-full max-w-5xl max-h-[85vh] overflow-y-auto"
                @click.outside="showCatalogModal = false">
                <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100">
                    <h2 class="text-2xl font-bold text-primary">Tambah Barang</h2>
                    <button @click="showCatalogModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-8">
                    <div class="flex items-center justify-between mb-6 flex-wrap gap-4">
                        <h3 class="text-xl font-bold text-secondary">Katalog Barang</h3>
                        <div class="flex items-center gap-3">
                            <input type="text" x-model="search" placeholder="Search items..."
                                class="px-4 py-2.5 w-64 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                            <select x-model="category"
                                class="px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="all">All Categories</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template x-for="item in filteredItems" :key="item.id">
                            <div class="bg-surface border border-slate-100 rounded-xl p-5">
                                <div
                                    class="h-32 bg-slate-100 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                                    <img :src="'/storage/' + item.image" x-show="item.image"
                                        class="h-full w-full object-contain">
                                </div>
                                <span
                                    class="inline-block bg-accent-bg text-accent text-xs font-semibold px-3 py-1 rounded-full mb-2"
                                    x-text="item.category"></span>
                                <h4 class="font-semibold text-secondary mb-1" x-text="item.name"></h4>
                                <p class="text-sm mb-3">
                                    Stock:
                                    <span :class="item.stock > 0 ? 'text-success' : 'text-danger'" class="font-semibold"
                                        x-text="item.stock + ' units'"></span>
                                </p>
                                <button type="button" @click="item.stock > 0 && openQtyModal(item)"
                                    :disabled="item.stock === 0"
                                    :class="item.stock === 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : 'bg-primary text-white'"
                                    class="w-full font-semibold py-2.5 rounded-lg">
                                    Tambah
                                </button>
                            </div>
                        </template>

                        <p x-show="filteredItems.length === 0" class="text-slate-500 col-span-3 text-center py-10">
                            Tidak ada barang ditemukan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal: Jumlah Barang (dipakai bareng untuk barang manapun yang dipilih dari katalog) --}}
        <div x-show="showQtyModal" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-6">
            <div class="bg-white rounded-2xl p-8 w-full max-w-sm" @click.outside="showQtyModal = false">
                <h2 class="text-2xl font-bold text-secondary mb-6">Jumlah Barang</h2>

                <div class="bg-slate-50 rounded-xl p-5 mb-6">
                    <label class="block font-semibold text-secondary mb-2">Jumlah *</label>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="quantity = Math.max(1, quantity - 1)"
                            class="w-11 h-11 rounded-lg bg-slate-200 text-secondary font-bold text-lg">−</button>
                        <div
                            class="flex-1 text-center bg-white border border-slate-200 rounded-lg py-2.5 font-semibold text-lg">
                            <span x-text="quantity"></span>
                        </div>
                        <button type="button" @click="quantity = Math.min(selectedItem?.stock ?? 1, quantity + 1)"
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
                        <input type="hidden" name="item_id" :value="selectedItem?.id">
                        <input type="hidden" name="quantity" :value="quantity">
                        <button type="submit" class="w-full bg-primary text-white font-semibold py-3 rounded-xl">
                            Selesai
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>