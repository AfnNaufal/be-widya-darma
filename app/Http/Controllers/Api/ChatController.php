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
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'visitor_token' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ]);

        $response = $this->chatbotService->processMessage(
            $validated['visitor_token'],
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
