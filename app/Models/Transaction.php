<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
/**
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property \Illuminate\Support\Carbon|null $returned_at
 */

class Transaction extends Model
{
    protected $fillable = [
        'loan_request_id', 'user_id', 'item_id', 'start_date', 'end_date', 'returned_at',
        'purpose', 'quantity', 'status', 'total_fee', 'document_path',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'returned_at' => 'date',
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

    // scope ini akan dipakai di Fase 4 untuk cek overlap tanggal
    public function scopeOverlapping($query, string $startDate, string $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate);
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
}
