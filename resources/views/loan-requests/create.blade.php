<x-app-layout>
    <div x-data="{
        showCatalogModal: false,
        showQtyModal: false,
        showConfirmModal: false,
        selectedItem: null,
        quantity: 1,
        search: '',
        category: 'all',
        startDate: '{{ $prefillDates['start_date'] }}',
        endDate: '{{ $prefillDates['end_date'] }}',
        fileName: null,
        fileSize: null,
        isImage: false,
        showFileError: false,
        catalogItems: {{ Illuminate\Support\Js::from(
            $catalogItems->map(
                fn($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'category' => $i->category,
                    'stock' => $i->available_stock,
                    'image' => $i->image,
                    'inCartQty' => $i->cart_quantity,
                ],
            ),
        ) }},
        cart: {{ Illuminate\Support\Js::from(
            $cartItems->map(
                fn($i) => [
                    'id' => $i->id,
                    'name' => $i->name,
                    'category' => $i->category,
                    'quantity' => $i->cart_quantity,
                ],
            ),
        ) }},
        get cartItemNames() {
            return this.cart.map(i => i.name);
        },
        get filteredItems() {
            return this.catalogItems.filter(i =>
                (this.category === 'all' || i.category === this.category) &&
                i.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        truncate(name) {
            return name.length > 20 ? name.slice(0, 20) + '…' : name;
        },
        openQtyModal(item) {
            this.selectedItem = item;
            this.quantity = item.inCartQty > 0 ? item.inCartQty : 1;
            this.showCatalogModal = false;
            this.showQtyModal = true;
        },
        formatDate(dateStr) {
            if (!dateStr) return '-';
            const d = new Date(dateStr);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
        },
        async addToCart() {
            const res = await fetch('{{ route('loan-cart.add') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({
                    item_id: this.selectedItem.id,
                    quantity: this.quantity,
                    start_date: this.startDate,
                    end_date: this.endDate,
                }),
            });

            if (!res.ok) {
                const data = await res.json();
                $dispatch('toast', { message: data.message ?? 'Gagal menambahkan barang.', type: 'danger' });
                return;
            }

            const data = await res.json();
            const existingIndex = this.cart.findIndex(i => i.id === data.item.id);

            if (existingIndex !== -1) {
                this.cart[existingIndex].quantity = data.item.quantity;
            } else {
                this.cart.push(data.item);
            }

            const catalogIndex = this.catalogItems.findIndex(i => i.id === data.item.id);
            if (catalogIndex !== -1) {
                this.catalogItems[catalogIndex].inCartQty = data.item.quantity;
            }

            this.showQtyModal = false;
            $dispatch('toast', { message: 'Barang berhasil ditambahkan ke keranjang.', type: 'success' });
        },
        async removeFromCart(itemId) {
            const res = await fetch('{{ url('/loan-cart') }}/' + itemId, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
            });

            if (res.ok) {
                this.cart = this.cart.filter(i => i.id !== itemId);

                const catalogIndex = this.catalogItems.findIndex(i => i.id === itemId);
                if (catalogIndex !== -1) {
                    this.catalogItems[catalogIndex].inCartQty = 0;
                }
            }
        },
        attemptSubmit() {
            this.showFileError = false;

            if (!this.$refs.loanForm.reportValidity()) {
                return;
            }
            if (this.cart.length === 0) {
                $dispatch('toast', { message: 'Keranjang masih kosong, tambahkan minimal 1 barang.', type: 'danger' });
                return;
            }
            if (!this.fileName) {
                this.showFileError = true;
                this.$refs.documentInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            this.showConfirmModal = true;
        }
    }">

        <div class="flex items-center gap-3 mb-4 sm:mb-6">
            <a href="{{ route('items.index') }}"
                class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-lg border border-slate-200 bg-surface text-secondary hover:bg-slate-50 transition shrink-0"
                aria-label="Kembali ke katalog">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-secondary">Pinjam Barang</h1>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-danger/10 border border-danger/30 text-danger text-sm px-4 py-3 rounded-lg">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 pb-32 lg:pb-0 lg:items-start">

            {{-- Form utama --}}
            <div class="lg:col-span-2 bg-surface rounded-2xl shadow-sm p-4 sm:p-6">
                <form method="POST" action="{{ route('loan-requests.store') }}" enctype="multipart/form-data"
                    class="space-y-5 sm:space-y-6" id="loanForm" x-ref="loanForm">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-secondary mb-1">Tanggal Mulai *</label>
                            <input type="date" name="start_date" x-model="startDate" :value="startDate" required
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block font-semibold text-secondary mb-1">Tanggal Kembali *</label>
                            <input type="date" name="end_date" x-model="endDate" required
                                class="w-full px-4 py-2.5 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-secondary mb-1">Catatan (Optional)</label>
                        <textarea name="purpose" rows="4" placeholder="Tambahkan informasi tambahan..."
                            class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">{{ old('purpose') }}</textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-secondary mb-1">Dokumen Pendukung *</label>

                        <label x-show="!fileName" @dragover.prevent @dragleave.prevent
                            @drop.prevent="
                                $refs.documentInput.files = $event.dataTransfer.files;
                                const f = $event.dataTransfer.files[0];
                                fileName = f?.name;
                                fileSize = f?.size;
                                isImage = f?.type.startsWith('image/');
                            "
                            class="flex flex-col items-center justify-center border-2 border-dashed border-slate-300 rounded-xl py-8 sm:py-12 px-4 cursor-pointer hover:bg-slate-50 transition text-center">

                            <div class="w-12 h-12 bg-slate-200 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-secondary" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 16V4m0 0L8 8m4-4l4 4M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2" />
                                </svg>
                            </div>

                            <p class="text-secondary font-medium text-sm sm:text-base">
                                <span class="hidden sm:inline">Drag & drop your file here, or click to browse</span>
                                <span class="sm:hidden">Tap untuk pilih file</span>
                            </p>
                            <p class="text-slate-400 text-xs sm:text-sm mt-1">PDF, JPG, PNG (max 5MB)</p>

                            <input type="file" name="document" class="hidden" accept=".pdf,.jpg,.jpeg,.png"
                                x-ref="documentInput"
                                @change="
                                       const f = $event.target.files[0];
                                       fileName = f?.name;
                                       fileSize = f?.size;
                                       isImage = f?.type.startsWith('image/');
                                   ">
                        </label>

                        <div x-show="fileName" x-cloak
                            class="flex items-center gap-3 sm:gap-4 border-2 border-success/30 bg-success/5 rounded-xl p-3 sm:p-4">
                            <div x-show="!isImage"
                                class="w-10 h-10 sm:w-12 sm:h-12 bg-danger/10 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-danger" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14 2v6h6" />
                                </svg>
                            </div>
                            <div x-show="isImage"
                                class="w-10 h-10 sm:w-12 sm:h-12 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" />
                                    <circle cx="8.5" cy="8.5" r="1.5" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5L5 21" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-secondary text-sm sm:text-base truncate" x-text="fileName">
                                </p>
                                <p class="text-xs sm:text-sm text-slate-500"
                                    x-text="fileSize ? (fileSize / 1024).toFixed(0) + ' KB' : ''"></p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <svg class="w-5 h-5 text-success" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                                <button type="button"
                                    @click="fileName = null; fileSize = null; $refs.documentInput.value = ''"
                                    class="text-slate-400 hover:text-danger p-2 -m-2" aria-label="Hapus file">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p x-show="showFileError" x-cloak class="text-danger text-sm mt-2 font-medium">
                            Dokumen pendukung wajib diunggah.
                        </p>
                    </div>

                    {{-- Tombol submit — versi normal, CUMA tampil di desktop --}}
                    <button type="button" @click="attemptSubmit()"
                        class="hidden lg:block w-full bg-primary text-white font-semibold py-3.5 rounded-xl hover:opacity-90 transition">
                        Kirim Permintaan
                    </button>
                </form>
            </div>

            {{-- Sidebar kanan --}}
            <div class="lg:sticky lg:top-6 lg:max-h-[calc(100vh-3rem)] lg:flex lg:flex-col">
                <button type="button" @click="showCatalogModal = true"
                    class="w-full flex items-center justify-center gap-2 border-2 border-dashed border-primary text-primary font-semibold py-3.5 sm:py-4 rounded-xl hover:bg-primary/5 transition shrink-0 mb-4 sm:mb-6">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Item
                </button>

                <div class="space-y-4 sm:space-y-6 lg:overflow-y-auto lg:pr-1">
                    <template x-for="cartItem in cart" :key="cartItem.id">
                        <div class="bg-surface rounded-2xl shadow-sm p-4 sm:p-6">
                            <div class="flex items-center justify-between mb-3 sm:mb-4">
                                <h3 class="font-bold text-secondary text-sm sm:text-base">Ringkasan Barang</h3>
                                <button type="button" @click="removeFromCart(cartItem.id)"
                                    class="text-slate-400 hover:text-danger p-2 -m-2"
                                    aria-label="Hapus dari keranjang">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                            <div class="space-y-2.5 sm:space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Nama Barang :</span>
                                    <span class="font-medium text-secondary text-right"
                                        x-text="truncate(cartItem.name)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Kategori:</span>
                                    <span class="font-medium text-secondary" x-text="cartItem.category"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Jumlah:</span>
                                    <span class="font-medium text-secondary"
                                        x-text="cartItem.quantity + ' unit'"></span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-500">Status:</span>
                                    <span
                                        class="bg-success/10 text-success text-xs font-semibold px-3 py-1 rounded-full">Tersedia</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Modal Tambah Barang --}}
        <div x-show="showCatalogModal" x-cloak
            class="fixed inset-0 bg-black/40 z-40 flex items-end sm:items-center justify-center sm:p-6">
            <div class="bg-white w-full h-full sm:h-auto sm:rounded-2xl sm:max-w-5xl sm:max-h-[85vh] overflow-y-auto flex flex-col"
                @click.outside="showCatalogModal = false">
                <div
                    class="flex items-center justify-between px-4 sm:px-8 py-4 sm:py-6 border-b border-slate-100 shrink-0">
                    <h2 class="text-xl sm:text-2xl font-bold text-primary">Tambah Barang</h2>
                    <button @click="showCatalogModal = false" class="text-slate-400 hover:text-slate-600 p-2 -m-2"
                        aria-label="Tutup modal">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-4 sm:p-8 overflow-y-auto">
                    <div class="mb-4 sm:mb-6">
                        <h3 class="text-lg sm:text-xl font-bold text-secondary mb-3">Katalog Barang</h3>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <input type="text" x-model="search" placeholder="Search items..."
                                class="px-4 py-2.5 w-full sm:w-64 rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                            <select x-model="category"
                                class="px-4 py-2.5 w-full sm:w-auto rounded-lg border border-slate-200 bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="all">All Categories</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4 sm:gap-6">
                        <template x-for="item in filteredItems" :key="item.id">
                            <div class="bg-surface border border-slate-100 rounded-xl p-4 sm:p-5 w-full sm:w-64">
                                <div
                                    class="h-28 sm:h-32 bg-slate-100 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                                    <img :src="'/storage/' + item.image" x-show="item.image"
                                        class="h-full w-full object-contain" onerror="this.style.display='none'">
                                </div>
                                <span
                                    class="inline-block bg-category-bg text-category text-xs font-semibold px-3 py-1 rounded-full mb-2"
                                    x-text="item.category"></span>
                                <h4 class="font-semibold text-secondary mb-1 text-sm sm:text-base" x-text="item.name">
                                </h4>
                                <p class="text-sm mb-3">
                                    Stock:
                                    <span :class="item.stock > 0 ? 'text-success' : 'text-danger'"
                                        class="font-semibold" x-text="item.stock + ' units'"></span>
                                </p>
                                <button type="button" @click="item.stock > 0 && openQtyModal(item)"
                                    :disabled="item.stock === 0"
                                    :class="item.stock === 0 ? 'bg-slate-200 text-slate-400 cursor-not-allowed' : (item
                                        .inCartQty > 0 ? 'bg-secondary text-white' : 'bg-primary text-white')"
                                    class="w-full font-semibold py-2.5 rounded-lg">
                                    <span
                                        x-text="item.inCartQty > 0 ? 'Di keranjang (' + item.inCartQty + ')' : 'Tambah'"></span>
                                </button>
                            </div>
                        </template>

                        <p x-show="filteredItems.length === 0" class="text-slate-500 w-full text-center py-10">
                            Tidak ada barang ditemukan.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Jumlah Barang — sekarang pakai AJAX, TIDAK reload halaman --}}
        <div x-show="showQtyModal" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4 sm:p-6">
            <div class="bg-white rounded-2xl p-6 sm:p-8 w-full max-w-sm" @click.outside="showQtyModal = false">
                <h2 class="text-xl sm:text-2xl font-bold text-secondary mb-5 sm:mb-6">Jumlah Barang</h2>

                <div class="bg-slate-50 rounded-xl p-4 sm:p-5 mb-5 sm:mb-6">
                    <label class="block font-semibold text-secondary mb-2">Jumlah *</label>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="quantity = Math.max(1, quantity - 1)"
                            class="w-11 h-11 shrink-0 rounded-lg bg-slate-200 text-secondary font-bold text-lg"
                            aria-label="Kurangi jumlah">−</button>
                        <div
                            class="flex-1 text-center bg-white border border-slate-200 rounded-lg py-2.5 font-semibold text-lg">
                            <span x-text="quantity"></span>
                        </div>
                        <button type="button" @click="quantity = Math.min(selectedItem?.stock ?? 1, quantity + 1)"
                            class="w-11 h-11 shrink-0 rounded-lg bg-primary text-white font-bold text-lg"
                            aria-label="Tambah jumlah">+</button>
                    </div>
                </div>

                <p class="text-center text-secondary font-medium mb-5 sm:mb-6 text-sm sm:text-base">
                    Total barang yang dipinjam: <span x-text="quantity"></span> unit
                </p>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" @click="showQtyModal = false"
                        class="bg-slate-100 text-secondary font-semibold py-3 rounded-xl">
                        Batal
                    </button>

                    <button type="button" @click="addToCart()"
                        class="w-full bg-primary text-white font-semibold py-3 rounded-xl">
                        Selesai
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Konfirmasi Peminjaman --}}
        <div x-show="showConfirmModal" x-cloak
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4 sm:p-6">
            <div class="bg-white rounded-2xl p-6 sm:p-8 w-full max-w-md text-center"
                @click.outside="showConfirmModal = false">
                <div
                    class="w-14 h-14 sm:w-16 sm:h-16 bg-warning/10 rounded-full flex items-center justify-center mx-auto mb-4 sm:mb-5">
                    <svg class="w-6 h-6 sm:w-7 sm:h-7 text-warning" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 8v5M12 16h.01" />
                    </svg>
                </div>

                <h2 class="text-xl sm:text-2xl font-bold text-secondary mb-2">Konfirmasi Peminjaman</h2>
                <p class="text-sm sm:text-base text-slate-500 mb-5 sm:mb-6">Apakah anda yakin ingin meminjam barang
                    ini?
                </p>

                <div class="bg-slate-50 rounded-xl p-4 sm:p-5 text-left mb-5 sm:mb-6">
                    <p class="font-semibold text-secondary mb-3 text-sm sm:text-base"
                        x-text="cartItemNames.join(', ')">
                    </p>
                    <div class="border-t border-slate-200 pt-3 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tanggal Peminjaman</span>
                            <span class="font-medium text-secondary" x-text="formatDate(startDate)"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Tanggal Kembali</span>
                            <span class="font-medium text-secondary" x-text="formatDate(endDate)"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button type="button" @click="showConfirmModal = false"
                        class="border border-slate-300 text-secondary font-semibold py-3 rounded-xl">
                        Batal
                    </button>
                    <button type="button" onclick="document.getElementById('loanForm').submit()"
                        class="bg-primary text-white font-semibold py-3 rounded-xl">
                        Pinjam
                    </button>
                </div>
            </div>
        </div>

        {{-- Tombol submit — sticky bottom bar, CUMA tampil di mobile --}}
        <div class="lg:hidden fixed inset-x-0 bg-white border-t border-slate-200 p-4 z-30"
            style="bottom: calc(56px + env(safe-area-inset-bottom));">
            <button type="button" @click="attemptSubmit()"
                class="w-full bg-primary text-white font-semibold py-3.5 rounded-xl">
                Kirim Permintaan
            </button>
        </div>

    </div>
</x-app-layout>
