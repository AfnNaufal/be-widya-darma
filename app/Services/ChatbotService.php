<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\KnowledgeBase;
use Illuminate\Support\Str;

class ChatbotService
{
    /**
     * Memproses pesan chat dari user, mencocokkan ke knowledge base, dan menyimpan riwayat.
     */
    public function processMessage(string $visitorToken, string $userMessageText): array
    {
        // 1. Ambil atau buat Chat Session berdasarkan visitor_token
        $session = ChatSession::firstOrCreate(
            ['visitor_token' => $visitorToken],
            ['started_at' => now()]
        );

        // 2. Simpan pesan user ke database
        $userMessage = ChatMessage::create([
            'session_id' => $session->session_id,
            'kb_id' => null,
            'sender' => 'user',
            'message' => $userMessageText,
            'intent' => null,
            'is_answered' => false,
            'tokens_used' => 0,
            'created_at' => now(),
        ]);

        // 3. Cari pengetahuan yang cocok dari Knowledge Base resmi SMA Widya Darma
        $matchedKnowledge = $this->searchKnowledgeBase($userMessageText);

        $intent = $matchedKnowledge ? $matchedKnowledge->category : 'general';
        $isAnswered = (bool) $matchedKnowledge;
        $botResponseText = '';
        $kbId = $matchedKnowledge ? $matchedKnowledge->kb_id : null;

        if ($matchedKnowledge) {
            $botResponseText = $matchedKnowledge->content;
        } else {
            $botResponseText = "Mohon maaf, saya belum memiliki informasi spesifik mengenai pertanyaan Anda. Silakan hubungi langsung pihak SMA Widya Darma melalui menu kontak atau datang langsung ke sekolah.";
        }

        // Estimasi token yang digunakan (1 kata ~ 1.3 token)
        $tokensUsed = (int) ceil(str_word_count($userMessageText . ' ' . $botResponseText) * 1.3);

        // 4. Simpan jawaban bot ke database
        $botMessage = ChatMessage::create([
            'session_id' => $session->session_id,
            'kb_id' => $kbId,
            'sender' => 'bot',
            'message' => $botResponseText,
            'intent' => $intent,
            'is_answered' => $isAnswered,
            'tokens_used' => $tokensUsed,
            'created_at' => now(),
        ]);

        return [
            'session_id' => $session->session_id,
            'visitor_token' => $visitorToken,
            'user_message' => $userMessageText,
            'bot_response' => $botResponseText,
            'kb_id' => $kbId,
            'intent' => $intent,
            'is_answered' => $isAnswered,
            'created_at' => $botMessage->created_at,
        ];
    }

    /**
     * Algoritma pencarian dasar di Knowledge Base berdasarkan keyword match / category.
     * Nantinya bisa diperluas dengan embedding / LLM semantic search.
     */
    protected function searchKnowledgeBase(string $query): ?KnowledgeBase
    {
        $keywords = array_filter(explode(' ', strtolower(trim($query))));

        $kbQuery = KnowledgeBase::where('is_active', true);

        // Pencarian bertahap: judul atau konten
        $result = KnowledgeBase::where('is_active', true)
            ->where(function ($q) use ($keywords, $query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%");

                foreach ($keywords as $word) {
                    if (strlen($word) > 2) {
                        $q->orWhere('title', 'LIKE', "%{$word}%")
                          ->orWhere('category', 'LIKE', "%{$word}%");
                    }
                }
            })
            ->first();

        return $result;
    }
}
