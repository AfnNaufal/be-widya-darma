<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\KnowledgeBase;
use Illuminate\Database\Seeder;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::first();
        $adminId = $admin ? $admin->admin_id : 1;

        $knowledges = [
            [
                'category' => 'profil',
                'title' => 'Profil Singkat dan Lokasi SMA Widya Darma',
                'content' => 'SMA Widya Darma adalah institusi pendidikan menengah atas yang berkomitmen mencetak generasi cerdas, berkarakter, dan berdaya saing tinggi. Informasi lebih lanjut dan alamat dapat dilihat pada profil resmi sekolah.',
            ],
            [
                'category' => 'visi_misi',
                'title' => 'Visi dan Misi SMA Widya Darma',
                'content' => 'Visi SMA Widya Darma adalah mewujudkan peserta didik yang unggul dalam prestasi, berbudi pekerti luhur, dan berwawasan teknologi.',
            ],
            [
                'category' => 'spmb',
                'title' => 'Informasi dan Persyaratan SPMB',
                'content' => 'Pendaftaran SPMB SMA Widya Darma dibuka secara berkala. Persyaratan umum meliputi fotokopi ijazah/SKL SMP, kartu keluarga, akta kelahiran, dan pas foto terbaru. Silakan pantau menu SPMB di website kami.',
            ],
            [
                'category' => 'ekskul',
                'title' => 'Ekstrakurikuler di SMA Widya Darma',
                'content' => 'SMA Widya Darma menyediakan berbagai ekstrakurikuler untuk mengembangkan minat bakat siswa, seperti Pramuka, Paskibra, Futsal, Basket, Paduan Suara, Rohis, dan Seni Tari.',
            ],
            [
                'category' => 'kontak',
                'title' => 'Kontak Resmi SMA Widya Darma',
                'content' => 'Untuk informasi lebih lengkap, Anda dapat menghubungi pihak sekolah melalui telepon atau email resmi yang tertera pada footer website, atau datang langsung ke ruang administrasi sekolah pada jam kerja.',
            ],
        ];

        foreach ($knowledges as $item) {
            KnowledgeBase::updateOrCreate(
                ['title' => $item['title']],
                [
                    'admin_id' => $adminId,
                    'category' => $item['category'],
                    'content' => $item['content'],
                    'is_active' => true,
                ]
            );
        }
    }
}
