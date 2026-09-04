<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon|null $returned_at
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_request_id',
        'user_id',
        'item_id',
        'start_date',
        'end_date',
        'returned_at',
        'purpose',
        'quantity',
        'status',
        'total_fee',
        'paid_at',
        'document_path',
        'return_photo',
        'return_note',
        'return_requested_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'returned_at' => 'date',
        'paid_at' => 'datetime',
        'return_requested_at' => 'datetime',
    ];

    public function loanRequest()
    {
        return $this->belongsTo(LoanRequest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // scope ini akan langsung dipakai di Fase 6 untuk perhitungan pendapatan
    public function scopeRevenueEligible($query)
    {
        return $query->whereIn('status', ['booked', 'completed']);
    }

    // scope ini dipakai AvailabilityService untuk cek overlap tanggal.
    //
    // PENTING: transaksi 'booked' yang end_date-nya sudah lewat hari ini
    // (telat) tapi belum ditandai selesai oleh admin, TETAP dianggap
    // menahan stok untuk rentang tanggal apa pun yang dicek — bukan cuma
    // sampai end_date aslinya. Kita tidak tahu kapan barangnya benar-benar
    // kembali secara fisik, jadi stok baru "bebas" lagi saat status
    // benar-benar diubah admin ke 'completed' (lihat Admin\TransactionController::complete),
    // bukan otomatis begitu tanggal jatuh temponya lewat.
    public function scopeOverlapping($query, string $startDate, string $endDate)
    {
        $today = now()->toDateString();

        return $query->where('start_date', '<=', $endDate)
            ->where(function ($q) use ($startDate, $today) {
                $q->where('end_date', '>=', $startDate)
                    ->orWhere('end_date', '<', $today);
            });
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'booked') {
            return now()->toDateString() > $this->end_date->toDateString();
        }

        if ($this->status === 'completed' && $this->returned_at) {
            return $this->returned_at->toDateString() > $this->end_date->toDateString();
        }

        return false;
    }

    // Denda dianggap "lunas" kalau memang tidak ada denda (total_fee 0),
    // atau admin sudah menandainya lunas secara eksplisit lewat paid_at.
    // Sengaja TIDAK otomatis lunas saat status jadi 'completed' — barang
    // kembali dan denda terbayar adalah dua kejadian yang berbeda.
    public function getIsPaidAttribute(): bool
    {
        return (float) $this->total_fee <= 0 || $this->paid_at !== null;
    }
}
