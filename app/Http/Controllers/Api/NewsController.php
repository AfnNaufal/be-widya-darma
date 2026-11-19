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
     * Mengambil daftar berita / kegiatan publik terbaru.
     * Mendukung filter ?category= (misal: Berita, Jurnal, Karya, Prestasi, Ekstrakurikuler).
     */
    public function index(Request $request): JsonResponse
    {
        $query = News::with(['galleries' => fn ($q) => $q->orderBy('sort_order')])
            ->withCount('visits')
            ->where(function ($q) {
                $q->where('status', 'Terbit')
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('published_at')
                            ->where('published_at', '<=', now());
                    });
            });

        if ($request->filled('category') && strtolower((string) $request->query('category')) !== 'semua') {
            $query->where('category', $request->string('category'));
        }

        $news = $query->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $news,
        ]);
    }

    /**
     * Mengambil detail satu berita / kegiatan berdasarkan slug, sekaligus mencatat kunjungan (PageVisit).
     */
    public function show(string $slug, Request $request): JsonResponse
    {
        $news = News::with(['galleries' => fn ($q) => $q->orderBy('sort_order')])
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
