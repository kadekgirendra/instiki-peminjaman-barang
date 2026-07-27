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
        {{-- Sidebar branding --}}
        <div class="lg:w-1/2 bg-secondary flex flex-col justify-center px-10 py-16 lg:px-20">
            <img src="{{ asset('images/logo-instiki.png') }}" alt="Logo INSTIKI" class="w-24 h-24 object-contain mb-8">

            <h1 class="text-4xl font-bold text-white leading-tight mb-4">
                Selamat datang di Sistem<br>Peminjaman INSTIKI
            </h1>
            <p class="text-slate-300 text-lg leading-relaxed max-w-md">
                Akses dan kelola inventaris kampus dengan mudah.
                Masuk untuk meminjam peralatan, lacak barang, dan
                sederhanakan pengalaman peminjaman di kampus.
            </p>

            <div class="flex gap-2 mt-10">
                <span class="h-1.5 w-16 rounded-full bg-primary"></span>
                <span class="h-1.5 w-16 rounded-full bg-warning"></span>
                <span class="h-1.5 w-16 rounded-full bg-slate-500"></span>
            </div>
        </div>

        {{-- Form card --}}
        <div class="lg:w-1/2 flex items-center justify-center px-6 py-16">
            <div class="w-full max-w-md bg-surface rounded-2xl shadow-lg p-10">
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
</html>