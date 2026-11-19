<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        protected ChatbotService $chatbotService
    ) {}

    /**
     * Mengirim pesan dari pengunjung ke AI Chatbot.
     * Mendukung field 'message' maupun 'question' (sesuai frontend SchoolChat.tsx).
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $hasQuestionField = $request->has('question') && ! $request->has('message');

        if ($hasQuestionField) {
            $request->merge(['message' => $request->input('question')]);
        }

        $validated = $request->validate([
            'visitor_token' => ['sometimes', 'required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-]+$/'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $visitorToken = $validated['visitor_token']
            ?? substr(hash('sha256', ($request->ip() ?? 'guest').'|'.($request->userAgent() ?? 'web')), 0, 32);

        $response = $this->chatbotService->processMessage(
            $visitorToken,
            $validated['message']
        );

        return response()->json([
            'success' => true,
            'data' => $response,
        ]);
    }

    /**
     * Mengambil riwayat obrolan berdasarkan visitor_token.
     */
    public function history(string $visitorToken): JsonResponse
    {
        if (! preg_match('/^[a-zA-Z0-9_\-]+$/', $visitorToken) || strlen($visitorToken) > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Format visitor_token tidak valid.',
            ], 422);
        }

        $session = ChatSession::where('visitor_token', $visitorToken)
            ->with(['messages' => function ($query) {
                $query->with('knowledgeBase:kb_id,title,category')
                    ->orderBy('created_at', 'asc');
            }])
            ->first();

        if (! $session) {
            return response()->json([
                'success' => true,
                'data' => [
                    'visitor_token' => $visitorToken,
                    'messages' => [],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }
}
