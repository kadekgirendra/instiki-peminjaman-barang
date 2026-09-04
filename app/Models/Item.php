<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

#[Fillable(['name', 'category', 'description', 'image', 'total_stock', 'daily_fine_rate'])]
class Item extends Model
{
    use HasFactory, SoftDeletes;

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public static function categories(): Collection
    {
        try {
            return Cache::remember('item_categories', 3600, function () {
                return static::select('category')
                    ->distinct()
                    ->pluck('category')
                    ->map(fn ($c) => trim($c))
                    ->unique()
                    ->values();
            });
        } catch (\Throwable $e) {
            // Kalau cache korup (race condition antar-request yang nulis cache
            // key yang sama bersamaan -> __PHP_Incomplete_Class saat dibaca),
            // buang cache yang rusak dan hitung ulang langsung dari database.
            // Ini supaya halaman TIDAK ikut down cuma gara-gara 1 baris cache.
            Cache::forget('item_categories');

            return static::select('category')
                ->distinct()
                ->pluck('category')
                ->map(fn ($c) => trim($c))
                ->unique()
                ->values();
        }

    }

    public static function forgetCategoriesCache(): void
    {
        Cache::forget('item_categories');
    }
}
