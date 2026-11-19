<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Extracurricular;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Program;
use App\Models\SchoolProfile;
use App\Models\Spmb;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class SchoolContentSeeder extends Seeder
{
    /**
     * Mengisi data awal konten website SMA Widya Darma sesuai struktur Frontend (fn-Widya-Darma).
     */
    public function run(): void
    {
        $admin = Admin::first();
        $adminId = $admin ? $admin->admin_id : 1;

        // 1. Profil Sekolah
        SchoolProfile::updateOrCreate(
            ['school_name' => 'SMA Widya Darma'],
            [
                'admin_id' => $adminId,
                'description' => 'SMA Widya Darma adalah institusi pendidikan menengah atas yang berkomitmen mencetak generasi cerdas, berkarakter, dan berdaya saing global melalui kurikulum modern dan pembinaan minat bakat terpadu.',
                'vision' => 'Mewujudkan peserta didik yang unggul dalam prestasi akademik dan non-akademik, berbudi pekerti luhur, serta berwawasan teknologi dan lingkungan.',
                'mission' => "1. Menyelenggarakan pembelajaran aktif, kreatif, dan berbasis teknologi informasi.\n2. Mengembangkan potensi dan bakat siswa melalui kegiatan ekstrakurikuler yang beragam.\n3. Membentuk karakter disiplin, santun, dan bertanggung jawab dalam kehidupan sehari-hari.",
                'history' => 'Didirikan sebagai wujud pengabdian di bidang pendidikan untuk menyediakan layanan pendidikan menengah berkualitas tinggi yang mudah diakses oleh masyarakat.',
                'address' => 'Jl. Pendidikan Raya No. 14, Jakarta',
                'phone' => '+62 812-3456-7890',
                'email' => 'info@widyadarma.sch.id',
                'instagram_url' => 'https://instagram.com/smawidyadarma',
                'youtube_url' => 'https://youtube.com/@smawidyadarma',
                'logo_url' => '/images/01.webp',
            ]
        );

        // 2. Direktori Guru & Tenaga Kependidikan (Sesuai src/lib/teacher-data.ts di Frontend)
        $teachers = [
            [
                'name' => 'Karina Fitriwulandari Ilham, S.Kom., M.Kom.',
                'code' => 'KFI',
                'role' => 'NJFA',
                'category' => 'MIPA',
                'subject' => 'Informatika & Rekayasa Komputer',
                'nip' => '19910412 201801 2 004',
                'nuptk' => '9234768123456789',
                'expertise' => ['Algoritma & Pemrograman', 'Struktur Data', 'Web Development', 'Kecerdasan Buatan Dasar'],
                'email' => 'karinafitriwulandari@widyadarma.sch.id',
                'phone' => '+62 812-3456-7890',
                'image' => '/images/50.webp',
                'education' => ['S1 Teknik Informatika - Institut Teknologi Bandung', 'S2 Magister Ilmu Komputer - Universitas Indonesia'],
                'bio' => 'Berdedikasi dalam membangun literasi teknologi peserta didik dengan pendekatan praktis berbasis proyek serta memotivasi siswa untuk berinovasi di dunia digital.',
                'status' => 'Guru Tetap',
                'sort_order' => 1,
                'address' => 'Jl. Melati No. 14, Jakarta Timur',
            ],
            [
                'name' => 'Erita Cicilia Febrianti, S.Tr.T., M.Tr.Kom',
                'code' => 'CCL',
                'role' => 'NJFA',
                'category' => 'MIPA',
                'subject' => 'Sistem Jaringan Komputer & Telekomunikasi',
                'nip' => '19930825 201902 2 006',
                'nuptk' => '8472910384729102',
                'expertise' => ['Network Security', 'Cloud Computing', 'Machine Learning', 'Network Architecture'],
                'email' => 'eritacicilia@widyadarma.sch.id',
                'phone' => '+62 813-8901-2345',
                'image' => '/images/34.webp',
                'education' => ['D4 / S.Tr.T Teknik Telekomunikasi - PENS', 'S2 Magister Terapan Komputer - Politeknik Elektronika Negeri Surabaya'],
                'bio' => 'Praktisi dan akademisi di bidang infrastruktur jaringan dan komputasi awan. Berpengalaman sebagai pembina tim olimpiade sains bidang informatika dan jaringan.',
                'status' => 'Guru Tetap',
                'sort_order' => 2,
                'address' => 'Jl. Surya Kencana No. 28, Jakarta Selatan',
            ],
            [
                'name' => 'Drs. Budi Santoso, M.Pd.',
                'code' => 'BST',
                'role' => 'NJFA',
                'category' => 'MIPA',
                'subject' => 'Matematika Peminatan & Terapan',
                'nip' => '19780514 200212 1 003',
                'nuptk' => '7382910485729183',
                'expertise' => ['Kalkulus Lanjut', 'Aljabar Linier', 'Statistika Terapan', 'Logika Matematika'],
                'email' => 'budisantoso@widyadarma.sch.id',
                'phone' => '+62 811-2345-6789',
                'image' => '/images/22.webp',
                'education' => ['S1 Pendidikan Matematika - Universitas Negeri Yogyakarta', 'S2 Magister Pendidikan Sains Matematika - UPI Bandung'],
                'bio' => 'Lebih dari 18 tahun mendedikasikan diri membina generasi muda dalam berpikir kritis matematis dan meraih berbagai medali Olimpiade Sains Nasional.',
                'status' => 'Guru Tetap',
                'sort_order' => 3,
                'address' => 'Jl. Anggrek Indah No. 5, Bekasi Barat',
            ],
            [
                'name' => 'Affifah Mutiara Pertiwi, S.Kom., M.Cs.',
                'code' => 'FFM',
                'role' => 'NJFA',
                'category' => 'MIPA',
                'subject' => 'Rekayasa Perangkat Lunak & Desain UI/UX',
                'nip' => '19941103 202003 2 008',
                'nuptk' => '6574839201948572',
                'expertise' => ['Human-Computer Interaction', 'UI/UX Design', 'Mobile App Development', 'Database Systems'],
                'email' => 'affifahmutiara@widyadarma.sch.id',
                'phone' => '+62 812-9876-5432',
                'image' => '/images/23.webp',
                'education' => ['S1 Ilmu Komputer - Universitas Gadjah Mada', 'S2 Master of Computer Science - Universitas Gadjah Mada'],
                'bio' => 'Fokus pada pengembangan antarmuka ramah pengguna serta pembelajaran interaktif yang mendorong siswa menciptakan aplikasi bernilai sosial tinggi.',
                'status' => 'Guru Tetap',
                'sort_order' => 4,
                'address' => 'Jl. Mawar Asri Blok C-12, Depok',
            ],
            [
                'name' => 'Yasinta Romadhona, S.Kom., M.Kom.',
                'code' => 'YSO',
                'role' => 'NJFA',
                'category' => 'MIPA',
                'subject' => 'Kecerdasan Buatan & Pemrosesan Data',
                'nip' => '19920719 201903 2 005',
                'nuptk' => '5483920184759203',
                'expertise' => ['Data Science', 'Machine Learning', 'Information Systems', 'Computer Vision'],
                'email' => 'yasintaromadhona@widyadarma.sch.id',
                'phone' => '+62 815-6789-0123',
                'image' => '/images/25.webp',
                'education' => ['S1 Teknik Informatika - Institut Teknologi Sepuluh Nopember', 'S2 Magister Teknik Informatika - ITS Surabaya'],
                'bio' => 'Pengajar inovatif yang mengintegrasikan pemodelan kecerdasan buatan dalam pembelajaran sains, aktif dalam penulisan modul ajar kurikulum digital.',
                'status' => 'Guru Tetap',
                'sort_order' => 5,
                'address' => 'Jl. Cempaka Putih Tengah No. 8, Jakarta Pusat',
            ],
            [
                'name' => 'Dra. Ratna Lestari, M.Si.',
                'code' => 'RLS',
                'role' => 'NJFA',
                'category' => 'Sosial & Humaniora',
                'subject' => 'Sosiologi & Antropologi Budaya',
                'nip' => '19750918 199903 2 002',
                'nuptk' => '4382910485920194',
                'expertise' => ['Sosiologi Kontemporer', 'Metodologi Penelitian Sosial', 'Antropologi Budaya Nusantara', 'Pendidikan Karakter Siswa'],
                'email' => 'ratnalestari@widyadarma.sch.id',
                'phone' => '+62 817-4567-8901',
                'image' => '/images/18.webp',
                'education' => ['S1 Sosiologi - Universitas Airlangga', 'S2 Magister Sains Sosiologi - Universitas Airlangga'],
                'bio' => 'Mendorong kepedulian sosial, kepekaan budaya, dan keterampilan riset ilmiah bagi generasi muda penerus bangsa.',
                'status' => 'Guru Tetap',
                'sort_order' => 6,
                'address' => 'Jl. Gatot Subroto No. 45, Jakarta Selatan',
            ],
        ];

        foreach ($teachers as $t) {
            Teacher::updateOrCreate(
                ['email' => $t['email']],
                ['admin_id' => $adminId, ...$t]
            );
        }

        // 3. Program Sekolah (3 Pilar Utama sesuai SchoolIntro di Frontend)
        $programs = [
            [
                'title' => 'Kurikulum Terstruktur & Adaptif',
                'description' => 'Pembelajaran aktif berbasis kurikulum nasional yang adaptif terhadap perkembangan sains dan teknologi.',
                'image_url' => '/images/33.webp',
            ],
            [
                'title' => 'Pembinaan Minat, Bakat & Etika',
                'description' => 'Pengembangan karakter siswa melalui bimbingan konseling, kepemimpinan OSIS, dan kegiatan ekstrakurikuler.',
                'image_url' => '/images/31.webp',
            ],
            [
                'title' => 'Lingkungan Belajar Nyaman & Modern',
                'description' => 'Fasilitas ruang belajar, perpustakaan, laboratorium sains, dan sarana olahraga yang representatif.',
                'image_url' => '/images/14.webp',
            ],
        ];

        Program::query()->delete();
        foreach ($programs as $prog) {
            Program::create([
                'admin_id' => $adminId,
                ...$prog,
            ]);
        }

        // 4. Ekstrakurikuler
        $extracurriculars = [
            [
                'name' => 'Pramuka Widya Darma',
                'description' => 'Pembinaan kemandirian, kepemimpinan, dan kepedulian lingkungan melalui kegiatan kepramukaan aktif.',
                'coach_name' => 'Drs. Budi Santoso, M.Pd.',
                'schedule' => 'Jumat, 15.00 - 17.00 WIB',
                'image_url' => '/images/48.webp',
            ],
            [
                'name' => 'Futsal & Bola Basket',
                'description' => 'Pengembangan bakat olahraga tim, kebugaran fisik, dan sportivitas dalam kejuaraan pelajar antar-sekolah.',
                'coach_name' => 'Ahmad Rifa\'i, M.Pd.',
                'schedule' => 'Rabu & Sabtu, 15.30 - 17.30 WIB',
                'image_url' => '/images/39.webp',
            ],
            [
                'name' => 'Seni Tari & Musik Modern',
                'description' => 'Wadah ekspresi seni budaya nusantara, paduan suara, dan ansambel musik siswa SMA Widya Darma.',
                'coach_name' => 'Dra. Ratna Lestari, M.Si.',
                'schedule' => 'Kamis, 15.00 - 17.00 WIB',
                'image_url' => '/images/40.webp',
            ],
        ];

        foreach ($extracurriculars as $ekskul) {
            Extracurricular::updateOrCreate(
                ['name' => $ekskul['name']],
                ['admin_id' => $adminId, ...$ekskul]
            );
        }

        // 5. Informasi SPMB (Sesuai src/lib/spmb-data.ts di Frontend)
        Spmb::updateOrCreate(
            ['academic_year' => '2026/2027'],
            [
                'admin_id' => $adminId,
                'status_label' => 'Buka',
                'headline' => 'Penerimaan Siswa Baru (SPMB)',
                'subheadline' => 'Bergabunglah bersama keluarga besar SMA Widya Darma untuk mewujudkan generasi unggul, berkarakter, dan berdaya saing global.',
                'registration_fee' => 'Rp 250.000',
                'contact_phone' => '+62 812-3456-7890',
                'contact_email' => 'spmb@widyadarma.sch.id',
                'brochure_url' => '/images/33.webp',
                'brochure_file_name' => 'Brosur-Resmi-SPMB-SMA-Widya-Darma-2026.webp',
                'brochure_uploaded_at' => '2026-10-20',
                'description' => 'Penerimaan Peserta Didik Baru (SPMB) SMA Widya Darma Tahun Ajaran 2026/2027 dibuka untuk jalur prestasi dan reguler.',
                'requirements' => implode("\n", [
                    'Mengisi formulir pendaftaran online atau offline di sekretariat sekolah',
                    'Salinan Rapor SMP/MTs semester 1 sampai 5 yang telah dilegalisir',
                    'Salinan Akta Kelahiran dan Kartu Keluarga (KK) calon siswa',
                    'Pas foto formal terbaru berwarna ukuran 3x4 (sebanyak 3 lembar)',
                    'Surat Keterangan Lulus (SKL) atau Ijazah SMP (jika sudah terbit)',
                    'Salinan piagam atau sertifikat prestasi kejuaraan (jalur prestasi)',
                ]),
                'schedule' => "Gelombang 1 (Early Bird): 01 Oktober - 31 Desember 2026\nGelombang 2 (Reguler): 01 Januari - 31 Maret 2027\nGelombang 3 (Sisa Kuota): 01 April - 30 Juni 2027",
                'waves' => [
                    [
                        'id' => 'wave-1',
                        'name' => 'Gelombang 1 (Early Bird)',
                        'period' => '01 Oktober - 31 Desember 2026',
                        'status' => 'Aktif',
                        'quota' => '120 Kursi',
                        'discount' => 'Diskon Biaya Masuk 20% + Bebas Biaya Formulir',
                    ],
                    [
                        'id' => 'wave-2',
                        'name' => 'Gelombang 2 (Reguler)',
                        'period' => '01 Januari - 31 Maret 2027',
                        'status' => 'Segera Dibuka',
                        'quota' => '90 Kursi',
                        'discount' => 'Diskon Biaya Masuk 10%',
                    ],
                    [
                        'id' => 'wave-3',
                        'name' => 'Gelombang 3 (Sisa Kuota)',
                        'period' => '01 April - 30 Juni 2027',
                        'status' => 'Segera Dibuka',
                        'quota' => '50 Kursi',
                        'discount' => 'Biaya Normal Tanpa Diskon',
                    ],
                ],
                'steps' => [
                    [
                        'step' => 1,
                        'title' => 'Pendaftaran Online / Offline',
                        'description' => 'Calon siswa atau orang tua mengisi formulir pendaftaran melalui website resmi atau hadir langsung di ruang sekretariat panitia SPMB.',
                    ],
                    [
                        'step' => 2,
                        'title' => 'Pengumpulan Berkas & Verifikasi',
                        'description' => 'Melengkapi dokumen persyaratan administrasi seperti rapor, KK, dan pas foto untuk ditinjau oleh tim verifikasi data.',
                    ],
                    [
                        'step' => 3,
                        'title' => 'Observasi & Tes Peminatan',
                        'description' => 'Mengikuti tes pemetaan akademik, tes minat bakat, dan sesi wawancara singkat bersama psikolog & guru BK sekolah.',
                    ],
                    [
                        'step' => 4,
                        'title' => 'Pengumuman & Daftar Ulang',
                        'description' => 'Hasil kelulusan diumumkan melalui portal SPMB online, dilanjutkan dengan proses registrasi ulang dan orientasi siswa baru.',
                    ],
                ],
                'faq' => [
                    [
                        'question' => 'Kapan pendaftaran SPMB SMA Widya Darma dibuka?',
                        'answer' => 'Pendaftaran Gelombang 1 dibuka mulai 01 Oktober hingga 31 Desember 2026. Pendaftaran dapat dilakukan kapan saja melalui website atau sekretariat sekolah.',
                    ],
                    [
                        'question' => 'Apakah ada program beasiswa bagi siswa berprestasi?',
                        'answer' => 'Ya, kami menyediakan beasiswa akademik (peringkat kelas/olimpiade) dan non-akademik (seni/olahraga) dengan potongan biaya pendidikan hingga 100%.',
                    ],
                ],
                'start_date' => '2026-10-01',
                'end_date' => '2027-06-30',
                'is_active' => true,
            ]
        );

        // 6. Galeri Kegiatan & Publikasi (Termasuk Kategori Berita, Prestasi, Karya, dan Ekstrakurikuler sesuai Frontend)
        $newsItems = [
            [
                'title' => 'Pentas Seni & Gelar Karya Siswa 2026',
                'slug' => 'pentas-seni-gelar-karya-siswa-2026',
                'author_name' => 'Drs. Budi Santoso, M.Pd.',
                'category' => 'Karya',
                'activity_date' => '08 Oktober 2026',
                'content' => 'Pentas Seni dan Gelar Karya Siswa SMA Widya Darma menghadirkan beragam karya lukis, pertunjukan musik daerah, dan kreasi teater modern. Kegiatan ini menjadi ajang apresiasi bakat seni serta wadah berekspresi seluruh siswa.',
                'image_url' => '/images/32.webp',
                'status' => 'Terbit',
                'crop_config' => ['zoom' => 1.15, 'x' => 0, 'y' => -5],
                'published_at' => '2026-10-08 09:00:00',
                'photos' => [
                    ['url' => '/images/32.webp', 'caption' => 'Pertunjukan teater dan drama musik siswa', 'order' => 1],
                    ['url' => '/images/31.webp', 'caption' => 'Pameran seni rupa dan lukisan buatan siswa', 'order' => 2],
                    ['url' => '/images/14.webp', 'caption' => 'Suasana kemeriahan panggung utama pensi', 'order' => 3],
                ],
            ],
            [
                'title' => 'Olimpiade Sains & Teknologi Provinsi',
                'slug' => 'olimpiade-sains-teknologi-provinsi',
                'author_name' => 'Dra. Ratna Lestari, M.Si.',
                'category' => 'Prestasi',
                'activity_date' => '02 Oktober 2026',
                'content' => 'Tim Olimpiade SMA Widya Darma berhasil membawa pulang 3 medali emas dalam ajang Olimpiade Sains & Teknologi tingkat provinsi. Karya inovasi siswa di bidang robotika otomasi mendapatkan apresiasi tinggi dari dewan juri.',
                'image_url' => '/images/33.webp',
                'status' => 'Terbit',
                'crop_config' => ['zoom' => 1.2, 'x' => 5, 'y' => 0],
                'published_at' => '2026-10-02 10:00:00',
                'photos' => [
                    ['url' => '/images/33.webp', 'caption' => 'Presentasi karya ilmiah tim robotika', 'order' => 1],
                    ['url' => '/images/32.webp', 'caption' => 'Sesi wawancara dan pengujian alat oleh juri', 'order' => 2],
                ],
            ],
            [
                'title' => 'Bakti Sosial & Donor Darah Widya Darma',
                'slug' => 'bakti-sosial-donor-darah-widya-darma',
                'author_name' => 'Ahmad Rifa\'i, M.Pd.',
                'category' => 'Berita',
                'activity_date' => '25 September 2026',
                'content' => 'PMI bekerja sama dengan PMR SMA Widya Darma menggelar aksi donor darah dan penyaluran paket sembako untuk masyarakat sekitar. Lebih dari 150 kantong darah terkumpul dari guru, staf, alumni, dan siswa.',
                'image_url' => '/images/14.webp',
                'status' => 'Terbit',
                'crop_config' => ['zoom' => 1.0, 'x' => 0, 'y' => 0],
                'published_at' => '2026-09-25 08:30:00',
                'photos' => [
                    ['url' => '/images/14.webp', 'caption' => 'Antrean pendonor darah di aula sekolah', 'order' => 1],
                    ['url' => '/images/31.webp', 'caption' => 'Penyerahan simbolis sembako kepada warga', 'order' => 2],
                    ['url' => '/images/32.webp', 'caption' => 'Panitia PMR bertugas melayani pendonor', 'order' => 3],
                ],
            ],
            [
                'title' => 'Latihan Gabungan Ekstrakurikuler Pramuka, Basket & Seni Tari',
                'slug' => 'latihan-gabungan-ekstrakurikuler-widya-darma',
                'author_name' => 'Drs. Budi Santoso, M.Pd.',
                'category' => 'Ekstrakurikuler',
                'activity_date' => '20 September 2026',
                'content' => 'Kegiatan ekstrakurikuler SMA Widya Darma meliputi Pramuka, Paskibra, Futsal, Bola Basket, Seni Tari, dan Paduan Suara yang rutin berlatih setiap minggu untuk mengembangkan bakat dan kepemimpinan siswa.',
                'image_url' => '/images/48.webp',
                'status' => 'Terbit',
                'crop_config' => ['zoom' => 1.0, 'x' => 0, 'y' => 0],
                'published_at' => '2026-09-20 15:00:00',
                'photos' => [
                    ['url' => '/images/48.webp', 'caption' => 'Kegiatan ekstrakurikuler Pramuka dan baris-berbaris', 'order' => 1],
                    ['url' => '/images/39.webp', 'caption' => 'Latihan musik dan paduan suara siswa', 'order' => 2],
                    ['url' => '/images/40.webp', 'caption' => 'Penampilan ekstrakurikuler seni tari tradisional', 'order' => 3],
                ],
            ],
        ];

        foreach ($newsItems as $item) {
            $news = News::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'admin_id' => $adminId,
                    'title' => $item['title'],
                    'author_name' => $item['author_name'],
                    'category' => $item['category'],
                    'activity_date' => $item['activity_date'],
                    'content' => $item['content'],
                    'image_url' => $item['image_url'],
                    'status' => $item['status'],
                    'crop_config' => $item['crop_config'],
                    'published_at' => $item['published_at'],
                ]
            );

            foreach ($item['photos'] as $index => $photo) {
                Gallery::updateOrCreate(
                    [
                        'news_id' => $news->news_id,
                        'image_url' => $photo['url'],
                    ],
                    [
                        'admin_id' => $adminId,
                        'title' => $photo['caption'],
                        'caption' => $photo['caption'],
                        'sort_order' => $photo['order'] ?? ($index + 1),
                        'is_cover' => $index === 0,
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
