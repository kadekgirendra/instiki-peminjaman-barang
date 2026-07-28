<x-app-layout>
    <h1 class="text-2xl font-bold text-secondary mb-6">History</h1>

    <div class="bg-surface rounded-2xl shadow-sm overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-secondary text-white">
                    <th class="text-left px-8 py-5 font-semibold">Barang</th>
                    <th class="text-left px-8 py-5 font-semibold">Tanggal</th>
                    <th class="text-left px-8 py-5 font-semibold">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($history as $loanRequestId => $group)
                    @php
                        $firstItem = $group->first();
                        $itemNames = $group->pluck('item.name')->implode(', ');
                        $isRejected = $firstItem->status === 'rejected';
                    @endphp
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-8 py-5 text-secondary">{{ $itemNames }}</td>
                        <td class="px-8 py-5 text-slate-500">{{ $firstItem->updated_at->translatedFormat('j F Y') }}</td>
                        <td class="px-8 py-5">
                            @if ($isRejected)
                                <span class="bg-danger text-white text-sm font-semibold px-5 py-2 rounded-lg">
                                    Ditolak
                                </span>
                            @else
                                <span class="bg-secondary text-white text-sm font-semibold px-5 py-2 rounded-lg">
                                    Selesai
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-slate-500 py-10">
                            Belum ada riwayat peminjaman.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>