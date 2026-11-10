<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\KnowledgeBase;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class ChatbotService
{
    /**
     * Kata-kata sapaan umum.
     *
     * @var array<int, string>
     */
    protected array $greetings = [
        'halo', 'hai', 'hello', 'hey', 'hei',
        'assalamualaikum', "assalamu'alaikum",
        'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam',
        'pagi', 'siang', 'sore', 'malam', 'permisi',
    ];

    /**
     * Kata kunci terkait SMA Widya Darma untuk verifikasi topik (rule-based).
     *
     * @var array<int, string>
     */
    protected array $schoolKeywords = [
        // Identitas & Umum
        'sma', 'widya', 'darma', 'sekolah', 'profil', 'visi', 'misi', 'sejarah',
        'akreditasi', 'kepala sekolah', 'struktur', 'organisasi', 'informasi', 'info',
        // Akademik & Program
        'jurusan', 'ipa', 'ips', 'bahasa', 'kurikulum', 'merdeka', 'kelas', 'pelajaran',
        'guru', 'pengajar', 'pendidik', 'prestasi', 'lulusan', 'alumni', 'program',
        // SPMB / PPDB
        'spmb', 'ppdb', 'daftar', 'pendaftaran', 'syarat', 'persyaratan', 'biaya',
        'spp', 'uang', 'formulir', 'jadwal', 'gelombang', 'kuota', 'seleksi', 'tes',
        'ujian', 'beasiswa', 'potongan', 'murid baru', 'siswa baru',
        // Fasilitas & Ekskul
        'ekskul', 'ekstrakurikuler', 'osis', 'pramuka', 'paskibra', 'basket', 'futsal',
        'voli', 'musik', 'tari', 'pmr', 'rohis', 'fasilitas', 'lab', 'laboratorium',
        'komputer', 'perpustakaan', 'lapangan', 'kantin', 'musholla', 'masjid', 'kegiatan',
        // Kontak & Lokasi
        'kontak', 'hubungi', 'alamat', 'lokasi', 'telepon', 'telp', 'wa', 'whatsapp',
        'email', 'instagram', 'sosmed', 'media sosial', 'peta', 'maps', 'jam kerja', 'jam buka',
    ];

    /**
     * Stopwords bahasa Indonesia yang diabaikan saat pencarian kata kunci.
     *
     * @var array<int, string>
     */
    protected array $stopwords = [
        'yang', 'untuk', 'pada', 'ke', 'para', 'namun', 'menurut', 'antara', 'dia', 'dua',
        'ia', 'seperti', 'jika', 'sehingga', 'kembali', 'dan', 'ini', 'karena', 'oleh',
        'saat', 'olehnya', 'hanya', 'apakah', 'bagaimana', 'berapa', 'kapan', 'dimana',
        'siapa', 'mengapa', 'kenapa', 'ada', 'adalah', 'yaitu', 'yakni', 'bisa', 'dapat',
        'saya', 'anda', 'kamu', 'kami', 'kita', 'mau', 'ingin', 'tolong', 'mohon',
    ];

    public function __construct(
        protected GeminiService $geminiService
    ) {}

    /**
     * Memproses pertanyaan pengguna sesuai alur SDD:
     * Validasi -> Sanitasi -> Cek Topik Sekolah -> Cari di KnowledgeBase -> (Jika ada) Panggil Gemini -> Simpan Sesi & Pesan.
     *
     * @return array{
     *     session_id: int,
     *     visitor_token: string,
     *     user_message: string,
     *     bot_response: string,
     *     kb_id: ?int,
     *     topic: ?string,
     *     intent: ?string,
     *     is_answered: bool,
     *     tokens_used: int,
     *     created_at: mixed
     * }
     */
    public function handleQuestion(string $visitorToken, string $userMessageText): array
    {
        $this->validateInput($userMessageText);

        $sanitizedText = $this->sanitize($userMessageText);

        // 1. Ambil atau buat sesi percakapan anonim pengunjung
        $session = ChatSession::firstOrCreate(
            ['visitor_token' => $visitorToken],
            ['started_at' => now()]
        );

        // 2. Simpan pesan pengguna ke riwayat
        $userMessage = ChatMessage::create([
            'session_id' => $session->session_id,
            'kb_id' => null,
            'sender' => 'user',
            'message' => $sanitizedText,
            'topic' => null,
            'is_answered' => false,
            'tokens_used' => 0,
            'created_at' => now(),
        ]);

        $botResponseText = '';
        $kbId = null;
        $topic = null;
        $isAnswered = false;
        $tokensUsed = 0;

        // 3. Cek sapaan ramah pembuka (Rule-based: langsung jawab tanpa konsumsi kuota LLM)
        if ($this->isGreetingOnly($sanitizedText)) {
            $botResponseText = 'Halo! Selamat datang di Pusat Layanan Informasi SMA Widya Darma. Ada yang bisa saya bantu seputar profil sekolah, pendaftaran (SPMB), program kegiatan, ekstrakurikuler, atau kontak resmi kami?';
            $topic = 'greeting';
            $isAnswered = true;
            $tokensUsed = 0;
        } elseif (! $this->isSchoolTopic($sanitizedText)) {
            // 4. Rule-based: jika di luar topik sekolah, tolak secara santun tanpa memanggil Gemini API
            $botResponseText = 'Mohon maaf, saya adalah asisten virtual resmi SMA Widya Darma. Saya hanya dapat melayani pertanyaan seputar SMA Widya Darma, seperti profil sekolah, pendaftaran murid baru (SPMB), program, ekstrakurikuler, fasilitas, dan kontak sekolah. Silakan ajukan pertanyaan terkait sekolah kami.';
            $topic = 'out_of_scope';
            $isAnswered = false;
            $tokensUsed = 0;
        } else {
            // 5. Cari informasi yang relevan di Knowledge Base resmi
            $matchedKnowledge = $this->searchKnowledgeBase($sanitizedText);

            if (! $matchedKnowledge) {
                // Sesuai SDD: Jika tidak ada data di KB, jangan panggil Gemini (hemat token & hindari halusinasi)
                $botResponseText = 'Mohon maaf, saya belum menemukan informasi resmi mengenai hal tersebut di basis data SMA Widya Darma. Silakan hubungi pihak sekolah secara langsung melalui menu kontak atau datang ke sekretariat SMA Widya Darma.';
                $topic = 'unknown';
                $isAnswered = false;
                $tokensUsed = 0;
            } else {
                // 6. Konteks ditemukan: susun konteks dan minta Gemini menghasilkan jawaban natural
                $kbId = $matchedKnowledge->kb_id;
                $topic = $matchedKnowledge->category;
                $context = $this->buildContext($matchedKnowledge);

                // Ambil hingga 4 pesan sebelumnya untuk multi-turn context
                $recentHistory = ChatMessage::where('session_id', $session->session_id)
                    ->where('message_id', '!=', $userMessage->message_id)
                    ->orderBy('message_id', 'desc')
                    ->take(4)
                    ->get()
                    ->reverse()
                    ->map(fn (ChatMessage $msg) => [
                        'role' => $msg->sender === 'user' ? 'user' : 'model',
                        'message' => $msg->message,
                    ])
                    ->values()
                    ->toArray();

                $geminiResult = $this->geminiService->generateAnswer($sanitizedText, $context, $recentHistory);

                if ($geminiResult['success']) {
                    $botResponseText = $geminiResult['text'];
                    $tokensUsed = $geminiResult['tokens_used'];
                    $isAnswered = true;
                } else {
                    // Skenario alternatif SDD: Fault tolerance jika API bermasalah, gunakan isi konten KB langsung
                    $botResponseText = $matchedKnowledge->content;
                    $tokensUsed = 0;
                    $isAnswered = true;
                }
            }
        }

        // 7. Simpan jawaban bot ke database
        $botMessage = ChatMessage::create([
            'session_id' => $session->session_id,
            'kb_id' => $kbId,
            'sender' => 'bot',
            'message' => $botResponseText,
            'topic' => $topic,
            'is_answered' => $isAnswered,
            'tokens_used' => $tokensUsed,
            'created_at' => now(),
        ]);

        return [
            'session_id' => $session->session_id,
            'visitor_token' => $visitorToken,
            'user_message' => $sanitizedText,
            'bot_response' => $botResponseText,
            'kb_id' => $kbId,
            'topic' => $topic,
            'intent' => $topic, // Alias kompatibilitas
            'is_answered' => $isAnswered,
            'tokens_used' => $tokensUsed,
            'created_at' => $botMessage->created_at,
        ];
    }

    /**
     * Alias kompatibilitas dengan pemanggil lama.
     *
     * @return array<string, mixed>
     */
    public function processMessage(string $visitorToken, string $userMessageText): array
    {
        return $this->handleQuestion($visitorToken, $userMessageText);
    }

    /**
     * Memvalidasi format input pengguna (NFR-09).
     *
     * @throws InvalidArgumentException
     */
    public function validateInput(string $message): void
    {
        $trimmed = trim($message);

        if ($trimmed === '') {
            throw new InvalidArgumentException('Pesan pertanyaan tidak boleh kosong.');
        }

        if (mb_strlen($trimmed) > 1000) {
            throw new InvalidArgumentException('Pesan pertanyaan maksimal 1000 karakter.');
        }
    }

    /**
     * Menyanitasi input pertanyaan pengguna untuk mencegah injection & prompt manipulation (DC-20).
     */
    public function sanitize(string $input): string
    {
        // 1. Bersihkan tag HTML / script
        $cleaned = strip_tags($input);

        // 2. Hapus karakter kontrol non-printable (kecuali newline & spasi)
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $cleaned) ?? $cleaned;

        // 3. Netralkan percobaan prompt injection umum
        $patterns = [
            '/ignore\s+(all\s+)?(previous\s+)?instructions/i',
            '/abaikan\s+(semua\s+)?instruksi\s+(sebelumnya)?/i',
            '/you\s+are\s+now/i',
            '/kamu\s+sekarang\s+adalah/i',
            '/jailbreak/i',
            '/system\s+prompt/i',
        ];

        $cleaned = preg_replace($patterns, '[disaring]', $cleaned) ?? $cleaned;

        // 4. Rapikan whitespace berlebih
        $cleaned = preg_replace('/\s+/', ' ', $cleaned) ?? $cleaned;

        return trim($cleaned);
    }

    /**
     * Menilai apakah pertanyaan seputar sekolah atau sapaan relevan (Rule-based).
     */
    public function isSchoolTopic(string $query): bool
    {
        $normalized = strtolower($query);

        // Jika mengandung sapaan
        foreach ($this->greetings as $greeting) {
            if (str_contains($normalized, $greeting)) {
                return true;
            }
        }

        // Jika mengandung kata kunci sekolah
        foreach ($this->schoolKeywords as $keyword) {
            if (str_contains($normalized, $keyword)) {
                return true;
            }
        }

        // Pertanyaan pendek umum (misal: "ada apa saja?", "kapan dimulai?") tetap diperiksa ke KB
        if (mb_strlen($normalized) <= 25 && (str_contains($normalized, '?') || str_contains($normalized, 'apa'))) {
            return true;
        }

        return false;
    }

    /**
     * Memeriksa apakah input hanya berupa sapaan tanpa pertanyaan spesifik.
     */
    protected function isGreetingOnly(string $query): bool
    {
        $clean = trim(preg_replace('/[^a-zA-Z\s]/', '', strtolower($query)) ?? '');
        $words = array_filter(explode(' ', $clean));

        if (count($words) === 0 || count($words) > 3) {
            return false;
        }

        foreach ($words as $word) {
            if (! in_array($word, ['halo', 'hai', 'hello', 'hey', 'pagi', 'siang', 'sore', 'malam', 'assalamualaikum', 'permisi', 'selamat'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Pencarian cerdas di tabel Knowledge Base berdasarkan query, judul, atau kata kunci.
     */
    public function searchKnowledgeBase(string $query): ?KnowledgeBase
    {
        $cleanQuery = strtolower(trim($query));

        // 1. Kata entitas sekolah umum yang tidak dijadikan penentu topik tunggal
        $schoolEntityWords = ['sma', 'widya', 'darma', 'sekolah', 'smas'];

        // 2. Ekstrak kata kunci yang bermakna (singkirkan stopwords dan nama entitas sekolah)
        $words = preg_split('/[\s,\.?!]+/', $cleanQuery) ?: [];
        $meaningfulKeywords = [];

        foreach ($words as $word) {
            $word = trim($word);
            if (mb_strlen($word) > 2 && ! in_array($word, $this->stopwords, true) && ! in_array($word, $schoolEntityWords, true)) {
                $meaningfulKeywords[] = $word;
            }
        }

        // Jika hanya menanyakan nama sekolah/profil umum (misal: "tentang SMA Widya Darma" atau "profil sekolah")
        if (empty($meaningfulKeywords)) {
            $hasSchoolName = false;
            foreach ($schoolEntityWords as $sew) {
                if (str_contains($cleanQuery, $sew)) {
                    $hasSchoolName = true;
                    break;
                }
            }

            if ($hasSchoolName) {
                return KnowledgeBase::where('is_active', true)
                    ->where(function (Builder $q) {
                        $q->where('category', 'profil')
                            ->orWhere('title', 'LIKE', '%Profil%');
                    })
                    ->first();
            }

            return null;
        }

        // 3. Pencarian kecocokan persis kategori atau judul dengan kata kunci yang ada
        $categoryMatch = KnowledgeBase::where('is_active', true)
            ->where(function (Builder $q) use ($meaningfulKeywords) {
                foreach ($meaningfulKeywords as $keyword) {
                    $q->orWhere('category', $keyword)
                        ->orWhere('category', 'LIKE', "%{$keyword}%");
                }
            })
            ->first();

        if ($categoryMatch) {
            return $categoryMatch;
        }

        // 4. Pencarian berbasis kata kunci terbobot
        $candidates = KnowledgeBase::where('is_active', true)
            ->where(function (Builder $q) use ($meaningfulKeywords) {
                foreach ($meaningfulKeywords as $keyword) {
                    $q->orWhereRaw('LOWER(title) LIKE ?', ["%{$keyword}%"])
                        ->orWhereRaw('LOWER(category) LIKE ?', ["%{$keyword}%"])
                        ->orWhereRaw('LOWER(content) LIKE ?', ["%{$keyword}%"]);
                }
            })
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        // Hitung skor relevansi sederhana berdasarkan kemunculan kata kunci di title, category, dan content
        $bestMatch = null;
        $highestScore = 0;

        foreach ($candidates as $kb) {
            $score = 0;
            $titleLower = strtolower($kb->title);
            $catLower = strtolower($kb->category);
            $contentLower = strtolower($kb->content);

            foreach ($meaningfulKeywords as $word) {
                if (str_contains($titleLower, $word)) {
                    $score += 6; // Bobot tinggi jika ada di judul
                }
                if (str_contains($catLower, $word)) {
                    $score += 5; // Bobot tinggi jika ada di kategori
                }
                if (str_contains($contentLower, $word)) {
                    $score += 2;
                }
            }

            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $kb;
            }
        }

        // Memerlukan skor minimal agar tidak salah mencocokkan informasi yang tidak relevan
        return $highestScore >= 3 ? $bestMatch : null;
    }

    /**
     * Menyusun format konteks Knowledge Base untuk dikirimkan ke model AI.
     */
    public function buildContext(KnowledgeBase $knowledgeBase): string
    {
        return "Kategori: {$knowledgeBase->category}\nTopik: {$knowledgeBase->title}\nInformasi Resmi:\n{$knowledgeBase->content}";
    }
}
