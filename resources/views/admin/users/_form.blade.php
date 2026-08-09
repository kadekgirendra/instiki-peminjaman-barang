@csrf

<div class="space-y-5 max-w-2xl">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-semibold text-secondary mb-1.5">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                placeholder="Contoh: I Wayan Yordi"
                class="w-full rounded-xl border border-slate-200 bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:bg-white transition">
            @error('name')
                <p class="text-danger text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-secondary mb-1.5">Username</label>
            <input type="text" name="username" value="{{ old('username', $user->username ?? '') }}" required
                placeholder="Contoh: yordi123"
                class="w-full rounded-xl border border-slate-200 bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:bg-white transition">
            @error('username')
                <p class="text-danger text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label class="block text-sm font-semibold text-secondary mb-1.5">NIM / NIDN</label>
        <input type="text" name="nim_nidn" value="{{ old('nim_nidn', $user->nim_nidn ?? '') }}" required
            placeholder="Contoh: 2201010001"
            class="w-full rounded-xl border border-slate-200 bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:bg-white transition">
        @error('nim_nidn')
            <p class="text-danger text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Reset Password --}}
    <div class="border-t border-slate-100 pt-5">
        <p class="text-sm font-semibold text-secondary mb-1">Reset Password</p>
        <p class="text-xs text-slate-400 mb-4">Isi kalau user ini lupa password. Kosongkan kedua kolom ini jika tidak
            ingin mengganti password.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-secondary mb-1.5">Password Baru</label>
                <input type="password" name="password" placeholder="Minimal 8 karakter"
                    class="w-full rounded-xl border border-slate-200 bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:bg-white transition">
                @error('password')
                    <p class="text-danger text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-secondary mb-1.5">Konfirmasi Password Baru</label>
                <input type="password" name="password_confirmation" placeholder="Ulangi password baru"
                    class="w-full rounded-xl border border-slate-200 bg-background px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary/30 focus:bg-white transition">
            </div>
        </div>
    </div>
</div>

<div class="flex justify-end gap-3 mt-8 pt-6 border-t border-slate-100 max-w-2xl">
    <a href="{{ route('admin.users.index') }}"
        class="px-6 py-3 rounded-full border border-slate-200 text-secondary font-semibold text-sm hover:bg-slate-50 transition">
        Batal
    </a>
    <button type="submit"
        class="px-8 py-3 rounded-full bg-primary text-white font-semibold text-sm hover:opacity-90 transition">
        Simpan Perubahan
    </button>
</div>