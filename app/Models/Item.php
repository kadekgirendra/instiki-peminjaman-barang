<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'category', 'description', 'image', 'total_stock', 'daily_fine_rate'])]
class Item extends Model
{
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
