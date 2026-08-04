<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use App\Models\Transaction;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(protected AvailabilityService $availability) {}

    public function index(Request $request)
    {
        $query = Transaction::with(['item', 'user', 'loanRequest']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%")
                    ->orWhere('nim_nidn', 'like', "%{$search}%"))
                    ->orWhereHas('item', fn($i) => $i->where('name', 'like', "%{$search}%"));
            });
        }

        $transactions = $query->get()->groupBy('loan_request_id')
            ->sortByDesc(fn($group) => $group->first()->loanRequest->created_at);

        $groups = $transactions->map(function ($group) {
            $loanRequest = $group->first()->loanRequest;
            $user = $group->first()->user;
            $statuses = $group->pluck('status')->unique();

            // Kalau masih ada item pending, permintaan dianggap Tertunda.
            // Kalau semua sudah diputuskan tapi campuran, anggap Disetujui (sudah diproses).
            if ($statuses->count() === 1) {
                $status = $statuses->first();
            } elseif ($statuses->contains('pending')) {
                $status = 'pending';
            } else {
                $status = 'booked';
            }

            $itemsLabel = $group->count() === 1
                ? $group->first()->item->name . ' - ' . $group->first()->item->category
                : $group->pluck('item.name')->implode(', ');

            $itemsList = $group->map(fn($trx) => [
                'id'          => $trx->id,
                'name'        => $trx->item->name,
                'quantity'    => $trx->quantity,
                'fine'        => (float) $trx->total_fee,
                'returned_at' => $trx->returned_at?->translatedFormat('j M Y'),
            ])->values();

            // Total denda seluruh barang dalam permintaan ini (cuma keisi kalau
            // sudah completed — sebelum itu total_fee semua barang masih 0).
            $totalFine = (float) $group->sum('total_fee');

            // Rentang tanggal barang benar-benar dikembalikan (bisa beda-beda per
            // barang kalau suatu saat fitur "selesai per-barang" dipisah; untuk
            // sekarang biasanya sama karena di-complete bareng lewat 1 form).
            $returnedAt = $group->pluck('returned_at')->filter()->max();

            $firstTrx = $group->first();
            $returnPhotoUrl = $firstTrx->return_photo ? asset('storage/' . $firstTrx->return_photo) : null;

            $documentUrl = null;
            $documentName = null;
            if ($loanRequest->document_path) {
                $documentUrl = asset('storage/' . $loanRequest->document_path);
                $documentName = 'Dokumen Pendukung.' . pathinfo($loanRequest->document_path, PATHINFO_EXTENSION);
            }

            $statusMeta = [
                'pending'   => ['label' => 'Tertunda',  'badge' => 'bg-warning/10 text-warning'],
                'booked'    => ['label' => 'Disetujui', 'badge' => 'bg-success/10 text-success'],
                'completed' => ['label' => 'Selesai',   'badge' => 'bg-info/10 text-info'],
                'rejected'  => ['label' => 'Ditolak',   'badge' => 'bg-danger/10 text-danger'],
            ][$status] ?? ['label' => $status, 'badge' => 'bg-slate-100 text-slate-500'];

            // "Terlambat" bukan status tersendiri di database — barangnya tetap 'booked'
            // (masih dianggap dipinjam), cuma labelnya diganti kalau end_date sudah lewat.
            // Ini murni tampilan, tidak mengubah $status supaya tombol "Selesaikan" & logic
            // approve/reject/complete tetap jalan seperti biasa.
            //
            // PENTING: pakai end_date dari tabel TRANSACTIONS (bukan loan_requests), diambil
            // yang paling akhir di antara barang dalam grup ini. Ini biar satu sumber sama
            // persis dengan yang dipakai AvailabilityService, accessor Transaction::is_overdue,
            // dan reminder bell — ketiganya baca transactions.end_date, bukan loan_requests.end_date.
            // Normalnya dua kolom itu sama (diisi bareng saat mahasiswa mengajukan), tapi kalau
            // beda (misal habis edit manual di DB), transactions.end_date yang menang.
            $latestEndDate = $group->max('end_date');

            $daysLate = null;
            if ($status === 'booked' && now()->toDateString() > $latestEndDate->toDateString()) {
                $daysLate = (int) $latestEndDate->diffInDays(now());
                $statusMeta = [
                    'label' => 'Terlambat ' . $daysLate . ' hari',
                    'badge' => 'bg-danger/10 text-danger',
                ];
            }

            // Untuk yang SUDAH completed: apakah waktu itu dikembalikan telat?
            // (beda dari $daysLate di atas, yang cuma untuk barang yang MASIH
            // booked dan belum dikembalikan sampai sekarang)
            $wasReturnedLate = false;
            $daysLateAtReturn = null;
            if ($status === 'completed' && $returnedAt && $returnedAt->toDateString() > $latestEndDate->toDateString()) {
                $wasReturnedLate = true;
                $daysLateAtReturn = (int) $latestEndDate->diffInDays($returnedAt);
            }

            return [
                'loan_request_id'      => $loanRequest->id,
                'user_name'            => $user->name,
                'user_nim'             => $user->nim_nidn,
                'items_label'          => $itemsLabel,
                'items_label_short'    => $group->count() === 1 ? $group->first()->item->name : $itemsLabel,
                'items_list'           => $itemsList,
                'tanggal_permintaan'   => $loanRequest->created_at->translatedFormat('j M Y'),
                'tanggal_pinjam'       => $loanRequest->start_date->translatedFormat('j M Y'),
                'tanggal_kembali'      => $loanRequest->end_date->translatedFormat('j M Y'),
                'status'               => $status,
                'status_label'         => $statusMeta['label'],
                'status_badge'         => $statusMeta['badge'],
                'is_overdue'           => $daysLate !== null,
                'days_late'            => $daysLate,
                'total_fine'           => $totalFine,
                'returned_at'          => $returnedAt?->translatedFormat('j M Y'),
                'was_returned_late'    => $wasReturnedLate,
                'days_late_at_return'  => $daysLateAtReturn,
                'catatan'              => $loanRequest->purpose,
                'document_url'         => $documentUrl,
                'document_name'        => $documentName,
                'approve_url'          => route('admin.loan-requests.approve', $loanRequest->id),
                'reject_url'           => route('admin.loan-requests.reject', $loanRequest->id),
                'complete_url'         => route('admin.loan-requests.complete', $loanRequest->id),
                'return_photo_url'     => $returnPhotoUrl,
                'return_note'          => $firstTrx->return_note,
                'has_return_request'   => $firstTrx->return_requested_at !== null,
            ];
        })->values();

        // Hitung jumlah per tab DARI GROUP LENGKAP (sebelum difilter) — dipakai buat
        // badge angka di tab filter. "late" itu bukan status di DB, cuma subset dari
        // 'booked' yang end_date-nya udah lewat (is_overdue true).
        $tabCounts = [
            'all'       => $groups->count(),
            'pending'   => $groups->where('status', 'pending')->count(),
            'booked'    => $groups->where('status', 'booked')->count(),
            'late'      => $groups->where('status', 'booked')->where('is_overdue', true)->count(),
            'completed' => $groups->where('status', 'completed')->count(),
            'rejected'  => $groups->where('status', 'rejected')->count(),
        ];

        // Filter opsional dari query string ?status=pending|booked|late|completed|rejected
        // — dipakai kartu Dashboard (misal "Pinjaman Aktif" -> ?status=booked) DAN tab
        // filter di halaman ini sendiri. 'late' = booked yang sudah lewat end_date saja
        // (bukan semua booked), dipakai link "Pengembalian Terlambat" dari Dashboard.
        $statusFilter = $request->get('status');

        if ($statusFilter === 'late') {
            $groups = $groups->where('status', 'booked')->where('is_overdue', true)->values();
        } elseif ($statusFilter) {
            $groups = $groups->where('status', $statusFilter)->values();
        }

        return view('admin.transactions.index', compact('groups', 'statusFilter', 'tabCounts'));
    }

    public function approve(LoanRequest $loanRequest)
    {
        $pendingItems = $loanRequest->transactions()->where('status', 'pending')->with('item')->get();

        if ($pendingItems->isEmpty()) {
            return back()->withErrors(['error' => 'Permintaan ini sudah diproses sebelumnya.']);
        }

        // WAJIB re-check ketersediaan tiap barang sebelum menyetujui seluruh permintaan
        foreach ($pendingItems as $trx) {
            if (! $this->availability->isAvailable(
                $trx->item,
                $trx->start_date->toDateString(),
                $trx->end_date->toDateString(),
                $trx->quantity
            )) {
                return back()->withErrors([
                    'error' => 'Stok "' . $trx->item->name . '" sudah terpakai transaksi lain di rentang tanggal tersebut.',
                ]);
            }
        }

        $loanRequest->transactions()->where('status', 'pending')->update(['status' => 'booked']);

        return back()->with('success', 'Permintaan berhasil disetujui.');
    }

    public function reject(LoanRequest $loanRequest)
    {
        $loanRequest->transactions()->where('status', 'pending')->update(['status' => 'rejected']);

        return back()->with('success', 'Permintaan ditolak.');
    }

    public function complete(Request $request, LoanRequest $loanRequest)
    {
        $bookedItems = $loanRequest->transactions()->where('status', 'booked')->get();

        if ($bookedItems->isEmpty()) {
            return back()->withErrors(['error' => 'Tidak ada barang yang perlu diselesaikan pada permintaan ini.']);
        }

        $validated = $request->validate([
            'returned_at' => 'required|date',
            'fines'       => 'nullable|array',
            'fines.*'     => 'nullable|numeric|min:0',
        ]);

        foreach ($bookedItems as $trx) {
            $trx->update([
                'returned_at' => $validated['returned_at'],
                'total_fee'   => $validated['fines'][$trx->id] ?? 0,
                'status'      => 'completed',
            ]);
        }

        return back()->with('success', 'Permintaan ditandai selesai.');
    }
}
