<?php

namespace App\Policies;

use App\Models\LoanRequest;
use App\Models\User;

class LoanRequestPolicy
{
    /**
     * Menentukan apakah $user boleh melihat/memproses pengajuan ini.
     * Dipakai untuk: buka form pengembalian, lihat detail pengajuan, dsb —
     * di mana pemiliknya sendiri (mahasiswa) yang boleh akses.
     */
    public function view(User $user, LoanRequest $loanRequest): bool
    {
        return $user->id === $loanRequest->user_id;
    }

    /**
     * Sama seperti view() — dipakai khusus untuk aksi kirim pengembalian barang.
     */
    public function returnItem(User $user, LoanRequest $loanRequest): bool
    {
        return $user->id === $loanRequest->user_id;
    }
}
