<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanRequest extends Model
{
    protected $fillable = ['user_id', 'start_date', 'end_date', 'purpose', 'document_path'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
