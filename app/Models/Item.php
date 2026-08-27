<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'category', 'description', 'image', 'total_stock', 'daily_fine_rate'])]
class Item extends Model
{
    use HasFactory, SoftDeletes;
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public static function categories(): \Illuminate\Support\Collection
    {
        return Cache::remember('item_categories', 3600, function () {
            return static::select('category')
                ->distinct()
                ->pluck('category')
                ->map(fn($c) => trim($c))
                ->unique()
                ->values();
        });
    }

    public static function forgetCategoriesCache(): void
    {
        Cache::forget('item_categories');
    }
}
