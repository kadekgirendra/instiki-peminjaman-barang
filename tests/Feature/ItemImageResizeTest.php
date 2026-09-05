<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ItemImageResizeTest extends TestCase
{
    use RefreshDatabase;

    public function test_uploaded_item_image_is_resized_and_saved_as_jpeg(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        // Buat gambar dummy 2000x3000px (mirip foto asli dari kamera HP)
        // TANPA butuh file foto sungguhan — UploadedFile::fake()->image()
        // otomatis generate gambar valid pakai GD.
        $bigImage = UploadedFile::fake()->image('barang-asli.png', 2000, 3000);

        $response = $this->actingAs($admin)->post(route('admin.items.store'), [
            'name' => 'Proyektor Epson',
            'category' => 'Elektronik',
            'description' => 'Proyektor untuk presentasi',
            'total_stock' => 5,
            'daily_fine_rate' => 5000,
            'image' => $bigImage,
        ]);

        $response->assertRedirect(route('admin.items.index'));

        $item = Item::where('name', 'Proyektor Epson')->firstOrFail();

        // File harus benar-benar tersimpan di disk...
        $this->assertNotNull($item->image);
        Storage::disk('public')->assertExists($item->image);

        // ...dengan ekstensi .jpg (diseragamkan, walau upload aslinya .png)...
        $this->assertStringEndsWith('.jpg', $item->image);

        // ...dan dimensi lebar sudah di-scale down ke maksimal 800px.
        $path = Storage::disk('public')->path($item->image);
        [$width, $height] = getimagesize($path);

        $this->assertLessThanOrEqual(800, $width);
        // Rasio aspek harus tetap terjaga (scaleDown tidak boleh distorsi gambar):
        // gambar asli 2000x3000 (rasio 2:3), jadi tinggi hasil resize harus
        // sekitar 1.5x lebarnya.
        $this->assertEqualsWithDelta($width * 1.5, $height, 2);
    }

    public function test_updating_item_image_replaces_old_file_and_updates_database(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $item = Item::factory()->create([
            'image' => 'items/gambar-lama.jpg',
        ]);
        Storage::disk('public')->put('items/gambar-lama.jpg', 'konten dummy');

        $newImage = UploadedFile::fake()->image('gambar-baru.png', 1500, 1500);

        $response = $this->actingAs($admin)->put(route('admin.items.update', $item), [
            'name' => $item->name,
            'category' => $item->category,
            'description' => $item->description,
            'total_stock' => $item->total_stock,
            'daily_fine_rate' => $item->daily_fine_rate,
            'image' => $newImage,
        ]);

        $response->assertRedirect(route('admin.items.index'));

        $item->refresh();

        // Kolom 'image' HARUS berubah ke path yang baru, bukan tetap ke path lama.
        $this->assertNotEquals('items/gambar-lama.jpg', $item->image);
        $this->assertNotNull($item->image);
        Storage::disk('public')->assertExists($item->image);

        // File lama harus sudah terhapus dari disk.
        Storage::disk('public')->assertMissing('items/gambar-lama.jpg');
    }
}
