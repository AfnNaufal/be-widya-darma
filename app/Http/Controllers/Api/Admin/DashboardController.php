<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Extracurricular;
use App\Models\Gallery;
use App\Models\KnowledgeBase;
use App\Models\News;
use App\Models\PageVisit;
use App\Models\Program;
use App\Models\Spmb;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Ringkasan status konten website sekolah (SRS FR-21).
     */
    public function stats(): JsonResponse
    {
        $activeSpmb = Spmb::where('is_active', true)->first();

        $contentSummary = [
            'news' => [
                'total' => News::count(),
                'published' => News::whereNotNull('published_at')->where('published_at', '<=', now())->count(),
                'draft' => News::whereNull('published_at')->orWhere('published_at', '>', now())->count(),
            ],
            'programs' => [
                'total' => Program::count(),
            ],
            'extracurriculars' => [
                'total' => Extracurricular::count(),
            ],
            'galleries' => [
                'total' => Gallery::count(),
            ],
            'knowledge_base' => [
                'total' => KnowledgeBase::count(),
                'active' => KnowledgeBase::where('is_active', true)->count(),
            ],
            'spmb' => [
                'is_active' => (bool) $activeSpmb,
                'active_academic_year' => $activeSpmb?->academic_year,
            ],
            'website_traffic' => [
                'total_page_views' => PageVisit::count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $contentSummary,
        ]);
    }

    /**
     * Statistik dan tren interaksi AI Chatbot (SRS FR-22 & FR-24).
     */
    public function chatbotStats(): JsonResponse
    {
        $totalSessions = ChatSession::count();
        $totalUserQuestions = ChatMessage::where('sender', 'user')->count();
        $totalBotResponses = ChatMessage::where('sender', 'bot')->count();

        $answeredCount = ChatMessage::where('sender', 'bot')->where('is_answered', true)->count();
        $unansweredCount = ChatMessage::where('sender', 'bot')->where('is_answered', false)->count();

        $answeredRate = $totalBotResponses > 0
            ? round(($answeredCount / $totalBotResponses) * 100, 1)
            : 0.0;

        $totalTokensUsed = (int) ChatMessage::sum('tokens_used');

        // Distribusi topik populer (SRS FR-22)
        $popularTopics = ChatMessage::whereNotNull('topic')
            ->where('topic', '!=', '')
            ->select('topic', DB::raw('count(*) as count'))
            ->groupBy('topic')
            ->orderByDesc('count')
            ->get();

        // 10 pertanyaan pengunjung terakhir yang belum terjawab / di luar konteks
        $unansweredBotMessages = ChatMessage::where('sender', 'bot')
            ->where('is_answered', false)
            ->whereIn('topic', ['unknown', 'out_of_scope'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        $recentUnanswered = [];
        foreach ($unansweredBotMessages as $botMsg) {
            // Ambil pesan user persis sebelum bot response ini dalam session yang sama
            $userMsg = ChatMessage::where('session_id', $botMsg->session_id)
                ->where('sender', 'user')
                ->where('created_at', '<=', $botMsg->created_at)
                ->orderByDesc('message_id')
                ->first();

            $recentUnanswered[] = [
                'question' => $userMsg?->message ?? '(Pertanyaan tidak tercatat)',
                'bot_response' => $botMsg->message,
                'topic' => $botMsg->topic,
                'created_at' => $botMsg->created_at,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => [
                    'total_sessions' => $totalSessions,
                    'total_user_questions' => $totalUserQuestions,
                    'total_bot_responses' => $totalBotResponses,
                    'answered_count' => $answeredCount,
                    'unanswered_count' => $unansweredCount,
                    'answered_rate_percentage' => $answeredRate,
                    'total_tokens_used' => $totalTokensUsed,
                ],
                'popular_topics' => $popularTopics,
                'recent_unanswered_questions' => $recentUnanswered,
            ],
        ]);
    }
}

