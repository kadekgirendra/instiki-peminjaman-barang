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
}
