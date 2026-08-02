@csrf

<div x-data="{
        preview: @js(isset($item) && $item->image ? asset('storage/' . $item->image) : null),
        onFile(e) {
            const file = e.target.files[0];
            if (file) this.preview = URL.createObjectURL(file);
        }
     }">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Kolom kiri: foto --}}
        <div>
            <label class="block text-sm font-semibold text-secondary mb-2">Foto Barang</label>

            <label
                class="relative flex flex-col items-center justify-center w-full aspect-square rounded-2xl border-2 border-dashed border-slate-200 bg-background overflow-hidden cursor-pointer hover:border-primary/40 transition">
                <template x-if="preview">
                    <img :src="preview" class="absolute inset-0 w-full h-full object-cover">
                </template>
                <template x-if="!preview">
                    <div class="flex flex-col items-center text-slate-400 px-4 text-center">
                        <svg class="w-8 h-8 mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.6">
                            <rect x="3" y="3" width="18" height="18" rx="2" />
                            <circle cx="9" cy="9" r="1.5" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 15l-5-5-9 9" />
                        </svg>
                        <span class="text-xs font-medium">Klik untuk unggah foto</span>
                        <span class="text-[11px] text-slate-300 mt-1">JPG/PNG, maks 2MB</span>
                    </div>
                </template>

                <input type="file" name="image" accept="image/png,image/jpeg,image/jpg" @change="onFile"
                    class="absolute inset-0 opacity-0 cursor-pointer">
            </label>

            @if (isset($item) && $item->image)
                <p class="text-xs text-slate-400 mt-2">Kosongkan kalau tidak ingin mengganti foto.</p>
            @endif
            @error('image')
            <p class="text-danger text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        {{-- Kolom kanan: detail --}}
        <div class="lg:col-span-2 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-secondary mb-1.5">Nama Barang</label>
                <input type="text" name="name" value="{{ old('name', $item->name ?? '') }}" required
                    placeholder="Contoh: Sound System"
                    class="w-full rounded-xl border border-slate-200 bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:bg-white transition">
                @error('name')
                <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-secondary mb-1.5">Kategori</label>
                    <input type="text" name="category" value="{{ old('category', $item->category ?? '') }}" required
                        placeholder="Contoh: Event" list="category-options"
                        class="w-full rounded-xl border border-slate-200 bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:bg-white transition">
                    @if (isset($categories))
                        <datalist id="category-options">
                            @foreach ($categories as $category)
                                <option value="{{ $category }}"></option>
                            @endforeach
                        </datalist>
                    @endif
                    @error('category')
                    <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-secondary mb-1.5">Total Stok</label>
                    <input type="number" name="total_stock" min="0"
                        value="{{ old('total_stock', $item->total_stock ?? 0) }}" required
                        class="w-full rounded-xl border border-slate-200 bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:bg-white transition">
                    @error('total_stock')
                    <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-secondary mb-1.5">Deskripsi</label>
                <textarea name="description" rows="4" placeholder="Deskripsi singkat barang (opsional)"
                    class="w-full rounded-xl border border-slate-200 bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:bg-white transition resize-none">{{ old('description', $item->description ?? '') }}</textarea>
                @error('description')
                <p class="text-danger text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100">
        <a href="{{ route('admin.items.index') }}"
            class="px-6 py-3 rounded-full border border-slate-200 text-secondary font-semibold text-sm hover:bg-slate-50 transition">
            Batal
        </a>
        <button type="submit"
            class="px-8 py-3 rounded-full bg-primary text-white font-semibold text-sm hover:opacity-90 transition">
            Simpan Barang
        </button>
    </div>
</div>