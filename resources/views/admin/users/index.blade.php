<x-admin-layout title="Kelola User">
    <div class="flex items-start justify-between flex-wrap gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-secondary">Kelola User</h1>
            <p class="text-slate-500 text-sm mt-0.5">Kelola akun mahasiswa dan dosen yang terdaftar</p>
        </div>

        <form method="GET">
            <div class="relative">
                <svg class="w-4 h-4 text-slate-400 absolute left-4 top-1/2 -translate-y-1/2" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7" />
                    <path stroke-linecap="round" d="M21 21l-3.5-3.5" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" onchange="this.form.submit()"
                    placeholder="Cari nama, username, NIM/NIDN..."
                    class="pl-10 pr-4 py-2.5 w-72 rounded-full border-0 bg-white shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-primary/30">
            </div>
        </form>
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
                    <th class="py-4 px-6 font-semibold">Nama</th>
                    <th class="py-4 px-6 font-semibold">Username</th>
                    <th class="py-4 px-6 font-semibold">NIM/NIDN</th>
                    <th class="py-4 px-6 font-semibold">Transaksi</th>
                    <th class="py-4 px-6 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr x-data="{ confirmDelete: false }" onclick="window.location='{{ route('admin.users.show', $user) }}'"
                        class="border-b border-slate-100 last:border-0 hover:bg-background/60 transition cursor-pointer">
                        <td class="py-4 px-6 font-medium text-secondary">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-9 h-9 rounded-full bg-secondary text-white flex items-center justify-center font-semibold text-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span>{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-slate-500">{{ $user->username }}</td>
                        <td class="py-4 px-6 text-slate-500">{{ $user->nim_nidn ?? '-' }}</td>
                        <td class="py-4 px-6 text-slate-500">{{ $user->transactions_count }} transaksi</td>
                        <td class="py-4 px-6" onclick="event.stopPropagation()">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.users.edit', $user) }}" title="Edit / reset password"
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

                                    <h3 class="text-xl font-bold text-secondary mb-2">Hapus User</h3>
                                    <p class="text-slate-500 mb-7">Apakah anda yakin ingin menghapus
                                        "{{ $user->name }}"?</p>

                                    <div class="flex gap-3">
                                        <button type="button" @click="confirmDelete = false"
                                            class="flex-1 border border-slate-200 text-secondary font-semibold py-3 rounded-full hover:bg-slate-50 transition">
                                            Batal
                                        </button>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
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
                        <td colspan="5" class="text-center text-slate-500 py-10">Belum ada user yang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($users->hasPages())
        <div class="mt-5">
            {{ $users->links() }}
        </div>
    @endif
</x-admin-layout>