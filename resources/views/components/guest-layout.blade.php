<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Peminjaman Barang Kampus INSTIKI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background min-h-screen">
    <div class="min-h-screen flex flex-col lg:flex-row">

        {{-- Panel branding — ringkas di mobile, lengkap di desktop --}}
        <div class="bg-secondary flex flex-col justify-center px-6 py-8 lg:w-1/2 lg:px-20 lg:py-16">

            <img src="{{ asset('images/logo-instiki.png') }}" alt="Logo INSTIKI"
                 class="w-12 h-12 object-contain mb-3 lg:w-24 lg:h-24 lg:mb-8">

            <h1 class="text-xl font-bold text-white leading-tight mb-0 lg:text-4xl lg:leading-tight lg:mb-4">
                <span class="lg:hidden">SiPinjam INSTIKI</span>
                <span class="hidden lg:inline">Selamat datang di Sistem<br>Peminjaman INSTIKI</span>
            </h1>

            {{-- Deskripsi panjang & progress dots CUMA muncul di desktop --}}
            <p class="hidden lg:block text-slate-300 text-lg leading-relaxed max-w-md">
                Akses dan kelola inventaris kampus dengan mudah.
                Masuk untuk meminjam peralatan, lacak barang, dan
                sederhanakan pengalaman peminjaman di kampus.
            </p>

            <div class="flex gap-2 mt-4 lg:mt-10">
                <span class="h-1.5 w-16 rounded-full bg-primary"></span>
                <span class="h-1.5 w-16 rounded-full bg-warning"></span>
                <span class="h-1.5 w-16 rounded-full bg-slate-500"></span>
            </div>
        </div>

        {{-- Form card --}}
        <div class="flex-1 flex items-center justify-center px-4 py-8 sm:px-6 lg:w-1/2 lg:py-16">
            <div class="w-full max-w-md bg-surface rounded-2xl shadow-lg p-6 sm:p-10">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>