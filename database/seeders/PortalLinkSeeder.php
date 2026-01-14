<?php

namespace Database\Seeders;

use App\Models\PortalLink;
use Illuminate\Database\Seeder;

class PortalLinkSeeder extends Seeder
{
    public function run(): void
    {
        $links = [
            [
                'title' => 'Chatbot Sales',
                'description' => 'Chatbot Sales digunakan untuk memonitor aktivitas sales. Menampilkan status aktivitas seperti "Visit", "Plan", dan "Lead," serta informasi tentang sales, pelanggan, dan wilayah.',
                'url' => '/sales',
                'badge_text' => 'Chatbot Sales',
                'icon' => 'CH',
                'sort_order' => 1,
            ],
            [
                'title' => 'RAB Online',
                'description' => 'RAB Online digunakan untuk membuat pengajuan RAB (Rencana Anggaran Biaya). Pengguna diminta untuk memilih jenis RAB yang akan diajukan.',
                'url' => '#',
                'badge_text' => 'RAB Online',
                'icon' => 'RA',
                'sort_order' => 2,
            ],
            [
                'title' => 'Form Peminjaman Barang Pusat',
                'description' => 'Form Peminjaman Barang Pusat. Kini anda dapat melakukan pengajuan peminjaman barang lebih mudah dengan menggunakan Program Approval.',
                'url' => '/gudang',
                'badge_text' => 'FPB', // From image roughly
                'icon' => 'FP',
                'sort_order' => 3,
            ],
            [
                'title' => 'Form Pickup',
                'description' => 'FORM PICK UP digunakan untuk mengumpulkan data permintaan pengambilan barang dari vendor, mencakup informasi email, nama vendor, detail tagihan, dll.',
                'url' => '#',
                'badge_text' => 'Form Pickup',
                'icon' => 'PI',
                'sort_order' => 4,
            ],
            [
                'title' => 'Sistem QC',
                'description' => 'Sistem QC digunakan untuk mengelola proses Quality Control barang. Sistem ini mengambil daftar barang dari SAP, membagi tugas QC kepada teknisi.',
                'url' => '/teknisi',
                'badge_text' => 'Sistem QC',
                'icon' => 'QC',
                'sort_order' => 5,
            ],
            [
                'title' => 'Biaya SAP',
                'description' => 'Sistem Biaya SAP digunakan untuk mengelola proses pengajuan vendor yang telah disetujui dan sesuai kesepakatan.',
                'url' => '/akunting',
                'badge_text' => 'Biaya SAP',
                'icon' => 'BI',
                'sort_order' => 6,
            ],
            [
                'title' => 'Kuesioner SAP',
                'description' => 'Form Penilaian Karyawan lintas divisi, yang digunakan untuk menilai kinerja seluruh divisi yang terkait dalam pekerjaan sehari-hari.',
                'url' => '#',
                'badge_text' => 'Kuesioner SAP',
                'icon' => 'KU',
                'sort_order' => 7,
            ],
        ];

        foreach ($links as $link) {
            PortalLink::updateOrCreate(
                ['title' => $link['title']],
                array_merge($link, ['slug' => \Illuminate\Support\Str::slug($link['title'])])
            );
        }
    }
}
