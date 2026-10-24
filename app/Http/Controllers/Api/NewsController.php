<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Services\NewsVisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function __construct(
        protected NewsVisitService $visitService
    ) {}

    /**
     * Mengambil daftar berita publik terbaru.
     */
    public function index(Request $request): JsonResponse
    {
        $news = News::with(['galleries'])
            ->withCount('visits')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $news,
        ]);
    }

    /**
     * Mengambil detail satu berita berdasarkan slug, sekaligus mencatat kunjungan (PageVisit).
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $news = News::with(['galleries'])
            ->withCount('visits')
            ->where('slug', $slug)
            ->firstOrFail();

        // Catat kunjungan secara unik ke tabel page_visits
        $this->visitService->recordVisit($news, $request);

        return response()->json([
            'success' => true,
            'data' => $news,
        ]);
    }
}
