<x-app-layout>
    <h1 class="text-2xl font-bold text-secondary mb-6">Pinjaman Saya</h1>

    {{-- Pinjaman Aktif --}}
    <div class="bg-success/5 border border-success/20 rounded-2xl p-6 mb-8">
        <h2 class="text-xl font-bold text-secondary">Pinjaman Aktif</h2>
        <p class="text-slate-500 text-sm mb-5">Barang saat ini</p>

        <div class="space-y-4">
            @forelse ($activeLoans as $loan)
                <div class="bg-surface rounded-xl p-5 flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="font-semibold text-secondary">{{ $loan->item->name }}</h3>
                            <span class="bg-success/10 text-success text-xs font-semibold px-3 py-1 rounded-full">
                                Dipinjam
                            </span>
                        </div>
                        <p class="text-sm text-slate-500">
                            Dipinjam : <span class="font-medium text-secondary">{{ $loan->start_date->translatedFormat('j M Y') }}</span>
                            &nbsp;&nbsp;
                            Dikembalikan : <span class="font-medium text-secondary">{{ $loan->end_date->translatedFormat('j M Y') }}</span>
                        </p>
                    </div>

                    <form method="POST" action="{{ route('transactions.request-return', $loan) }}">
                        @csrf
                        <button type="submit"
                                class="bg-secondary text-white font-semibold px-6 py-2.5 rounded-lg hover:opacity-90 transition">
                            Kembalikan
                        </button>
                    </form>
                </div>
            @empty
                <p class="text-slate-500 text-center py-6">Tidak ada barang yang sedang dipinjam.</p>
            @endforelse
        </div>
    </div>

    {{-- Permintaan --}}
    <div>
        <h2 class="text-xl font-bold text-secondary mb-4">Permintaan</h2>

        <div class="space-y-3">
            @forelse ($requests as $request)
                @php
                    $statusMap = [
                        'pending' => ['label' => 'Tertunda', 'class' => 'bg-warning/10 text-warning', 'note' => 'Tunggu disetujui'],
                        'booked'  => ['label' => 'Disetujui', 'class' => 'bg-primary/10 text-primary', 'note' => 'Siap diambil'],
                    ];
                    $status = $statusMap[$request->status];
                @endphp

                <div class="bg-surface rounded-xl p-5 flex items-center justify-between flex-wrap gap-4 border border-slate-100">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h3 class="font-semibold text-secondary">{{ $request->item->name }}</h3>
                            <span class="{{ $status['class'] }} text-xs font-semibold px-3 py-1 rounded-full">
                                {{ $status['label'] }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-500">
                            Diminta : {{ $request->created_at->translatedFormat('j M Y') }}
                        </p>
                    </div>

                    <span class="text-sm text-slate-400 italic">{{ $status['note'] }}</span>
                </div>
            @empty
                <p class="text-slate-500 text-center py-6">Belum ada permintaan peminjaman.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>