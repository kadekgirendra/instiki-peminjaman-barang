<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Kelola User hanya menampilkan akun mahasiswa/dosen (role: user).
        // Akun admin utama tidak ditampilkan di sini karena cuma ada satu
        // dan tidak dikelola lewat halaman ini.
        $query = User::query()->where('role', 'user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('nim_nidn', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount('transactions')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $statusMeta = [
            'pending' => ['label' => 'Tertunda', 'badge' => 'bg-warning/10 text-warning'],
            'booked' => ['label' => 'Disetujui', 'badge' => 'bg-success/10 text-success'],
            'completed' => ['label' => 'Selesai', 'badge' => 'bg-info/10 text-info'],
            'rejected' => ['label' => 'Ditolak', 'badge' => 'bg-danger/10 text-danger'],
        ];

        // Dikelompokkan per loan_request supaya satu pengajuan (bisa berisi
        // beberapa barang sekaligus) tampil sebagai satu baris riwayat,
        // sama seperti pola di admin.transactions.index.
        $transactions = Transaction::with(['item', 'loanRequest'])
            ->where('user_id', $user->id)
            ->get()
            ->groupBy('loan_request_id')
            ->sortByDesc(fn($group) => $group->first()->loanRequest->created_at)
            ->map(function ($group) use ($statusMeta) {
                $loanRequest = $group->first()->loanRequest;
                $statuses = $group->pluck('status')->unique();
                $status = $statuses->count() === 1
                    ? $statuses->first()
                    : ($statuses->contains('pending') ? 'pending' : 'booked');

                // Rincian barang dalam pengajuan ini beserta jumlah unit yang
                // dipinjam masing-masing (bukan cuma jumlah baris/jenis barang).
                $itemsDetail = $group->map(fn($trx) => [
                    'name' => $trx->item->name,
                    'category' => $trx->item->category,
                    'quantity' => $trx->quantity,
                    'fine' => (float) $trx->total_fee,
                ])->values();

                $totalFine = (float) $group->sum('total_fee');

                // Status pembayaran — sama definisinya dengan yang dipakai di
                // Admin\TransactionController::index(): lunas kalau memang tidak
                // ada denda, atau semua barang completed dalam pengajuan ini
                // sudah ditandai admin lewat paid_at.
                $completedItems = $group->where('status', 'completed');
                $isPaid = $totalFine <= 0
                    || ($completedItems->isNotEmpty() && $completedItems->every(fn($trx) => $trx->paid_at !== null));

                return [
                    'loan_request_id' => $loanRequest->id,
                    'items_detail' => $itemsDetail,
                    'total_unit' => $itemsDetail->sum('quantity'),
                    'tanggal_pinjam' => $loanRequest->start_date->translatedFormat('j M Y'),
                    'tanggal_kembali' => $loanRequest->end_date->translatedFormat('j M Y'),
                    'status' => $status,
                    'status_label' => $statusMeta[$status]['label'] ?? $status,
                    'status_badge' => $statusMeta[$status]['badge'] ?? 'bg-slate-100 text-slate-500',
                    'total_fine' => $totalFine,
                    'is_paid' => $isPaid,
                    'jumlah_jenis' => $group->count(),
                ];
            })
            ->values();

        // Ringkasan kecil di kartu profil: total pengajuan, sedang dipinjam, selesai.
        $summary = [
            'total' => $transactions->count(),
            'active' => $transactions->where('status', 'booked')->count(),
            'completed' => $transactions->where('status', 'completed')->count(),
        ];

        // Rekap per barang: barang apa saja yang pernah dipinjam user ini dan
        // berapa total unit + berapa kali dipinjam, diurutkan dari yang paling
        // sering. Dihitung dari transaksi mentah, bukan dari $transactions yang
        // sudah dikelompokkan per pengajuan.
        $itemSummary = Transaction::with('item')
            ->where('user_id', $user->id)
            ->get()
            ->groupBy('item_id')
            ->map(function ($group) {
                $item = $group->first()->item;

                return [
                    'name' => $item->name,
                    'category' => $item->category,
                    'total_unit' => $group->sum('quantity'),
                    'total_pinjam' => $group->count(),
                ];
            })
            ->sortByDesc('total_unit')
            ->values();

        return view('admin.users.show', compact('user', 'transactions', 'summary', 'itemSummary'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(AdminUserRequest $request, User $user)
    {
        $validated = $request->validated();

        // Kalau field password dikosongkan saat edit, jangan timpa password lama.
        // Ini juga jadi alasan utama halaman edit ada: admin reset password
        // user yang lupa, tanpa perlu user itu daftar ulang.
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // Sama seperti Item — User sekarang pakai SoftDeletes, jadi aman dihapus
        // meski masih punya riwayat transaksi. Datanya tidak hilang, cuma
        // disembunyikan dari query normal (deleted_at terisi).
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }
}
