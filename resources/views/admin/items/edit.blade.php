<x-app-layout title="Edit Barang">
    <a href="{{ route('admin.items.index') }}"
        class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-secondary mb-4 transition">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Kembali ke Inventaris
    </a>

    <h1 class="text-2xl font-bold text-secondary mb-6">Edit Barang</h1>

    <div class="bg-surface rounded-2xl shadow-sm p-7 max-w-4xl">
        <form method="POST" action="{{ route('admin.items.update', $item) }}" enctype="multipart/form-data">
            @method('PUT')
            @include('admin.items._form')
        </form>
    </div>
</x-app-layout>