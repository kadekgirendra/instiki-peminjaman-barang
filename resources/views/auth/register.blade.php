<x-guest-layout>
    <h1 class="text-3xl font-bold text-secondary mb-6">Buat Akun</h1>
    <p class="text-slate-500 mt-1 mb-6">Masukan informasi di bawah</p>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block font-semibold text-secondary mb-1">Nama User</label>
            <input type="text" name="username" value="{{ old('username') }}"
                   placeholder="Masukan nama user"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">
            @error('username')
                <p class="text-danger text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-semibold text-secondary mb-1">Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   placeholder="Masukan nama lengkap"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">
            @error('name')
                <p class="text-danger text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block font-semibold text-secondary mb-1">NIM</label>
            <input type="text" name="nim_nidn" value="{{ old('nim_nidn') }}"
                   placeholder="Masukan NIM"
                   class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-primary">
            @error('nim_nidn')
                <p class="text-danger text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="{ show: false }">
            <label class="block font-semibold text-secondary mb-1">Password</label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" name="password"
                       placeholder="Masukan password"
                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-primary">
                <button type="button" @click="show = !show"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="text-danger text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div x-data="{ show: false }">
            <label class="block font-semibold text-secondary mb-1">Confirm Password</label>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" name="password_confirmation"
                       placeholder="Masukan ulang password"
                       class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 pr-12 focus:outline-none focus:ring-2 focus:ring-primary">
                <button type="button" @click="show = !show"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                    </svg>
                </button>
            </div>
            @error('password_confirmation')
                <p class="text-danger text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full bg-primary text-white font-semibold py-3 rounded-lg hover:opacity-90 transition">
            Daftar
        </button>

        <p class="text-center text-sm text-slate-500 mt-4">
            Sudah punya akun? <a href="{{ route('login') }}" class="text-primary font-semibold">Login</a>
        </p>
    </form>
</x-guest-layout>