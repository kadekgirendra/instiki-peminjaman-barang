<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Kamera Canon EOS 6D Mark II',
                'category' => 'Multimedia',
                'description' => 'Kamera DSLR full-frame kelas profesional yang sangat ideal untuk keperluan dokumentasi acara kampus, produksi video, dan fotografi tingkat lanjut. Sudah dilengkapi dengan lensa standar zoom 24-105mm yang serbaguna untuk berbagai kondisi pemotretan.',
                'total_stock' => 4,
                'daily_fine_rate' => 5000,
                'image' => null,
            ],
            [
                'name' => 'Meja Lipat Portable',
                'category' => 'Event',
                'description' => 'Meja lipat praktis berwarna putih yang ringan dan mudah dipindahkan. Sangat cocok digunakan untuk keperluan stand registrasi, meja panitia, bazar, atau kegiatan operasional kepanitiaan acara kampus lainnya.',
                'total_stock' => 20,
                'daily_fine_rate' => 5000,
                'image' => null,
            ],
            [
                'name' => 'Speaker JBL Full-Range',
                'category' => 'Event',
                'description' => 'Speaker berdaya tinggi dengan jangkauan suara luas (full-range) dan bass yang kuat. Cocok digunakan untuk mendukung kebutuhan audio pada acara skala menengah hingga besar seperti seminar, workshop, atau acara hiburan di area kampus.',
                'total_stock' => 5,
                'daily_fine_rate' => 5000,
                'image' => null,
            ],
            [
                'name' => 'Handy Talky (HT)',
                'category' => 'Event',
                'description' => 'Perangkat komunikasi radio dua arah yang praktis. Barang wajib bagi panitia lapangan untuk memudahkan koordinasi, mobilitas, dan penyampaian informasi secara cepat (real-time) selama acara kampus berlangsung.',
                'total_stock' => 10,
                'daily_fine_rate' => 5000,
                'image' => null,
            ],
            [
                'name' => 'Mikrofon Dinamis Defender',
                'category' => 'Event',
                'description' => 'Mikrofon genggam dinamis yang dilengkapi dengan tombol on/off. Memiliki kualitas penangkapan suara vokal yang baik, sangat pas digunakan oleh MC, moderator, atau pembicara dalam kegiatan rapat, presentasi, maupun pentas seni.',
                'total_stock' => 10,
                'daily_fine_rate' => 5000,
                'image' => null,
            ],
            [
                'name' => 'Proyektor Epson',
                'category' => 'Multimedia',
                'description' => 'Proyektor dengan tingkat kecerahan tinggi dan proyeksi gambar yang tajam. Sangat direkomendasikan untuk mendukung kegiatan belajar mengajar di kelas, sidang skripsi, rapat organisasi, maupun seminar.',
                'total_stock' => 6,
                'daily_fine_rate' => 5000,
                'image' => null,
            ],
            [
                'name' => 'Mic Wireless Rode GO II',
                'category' => 'Event',
                'description' => 'Sistem mikrofon nirkabel (wireless) ultra-ringkas dengan kualitas audio profesional. Sangat disarankan untuk kegiatan vlogging, liputan jurnalistik kampus, wawancara, atau perekaman video dokumentasi yang membutuhkan mobilitas tinggi tanpa gangguan kabel.',
                'total_stock' => 5,
                'daily_fine_rate' => 5000,
                'image' => null,
            ],
            [
                'name' => 'Tripod Takara ECO-196A',
                'category' => 'Multimedia',
                'description' => 'Tripod kamera yang ringan namun kokoh untuk menopang kamera DSLR, mirrorless, atau smartphone. Berfungsi untuk menjaga stabilitas gambar agar tidak shaky saat melakukan perekaman video atau pengambilan foto jarak jauh (long exposure).',
                'total_stock' => 5,
                'daily_fine_rate' => 5000,
                'image' => null,
            ],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }

        Item::forgetCategoriesCache();
    }
}
