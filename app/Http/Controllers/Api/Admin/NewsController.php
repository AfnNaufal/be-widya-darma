<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsRequest;
use App\Models\News;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    /**
     * Daftar seluruh berita (termasuk draft yang belum di-publish).
     */
    public function index(Request $request): JsonResponse
    {
        $news = News::with(['admin:admin_id,name', 'galleries'])
            ->withCount('visits')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $news,
        ]);
    }

    /**
     * Buat berita baru (slug otomatis di-generate dari title jika dikosongkan).
     */
    public function store(NewsRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        $news = News::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil ditambahkan.',
            'data' => $news,
        ], 201);
    }

    /**
     * Detail berita berdasarkan news_id.
     */
    public function show(News $news): JsonResponse
    {
        $news->load(['admin:admin_id,name', 'galleries'])->loadCount('visits');

        return response()->json([
            'success' => true,
            'data' => $news,
        ]);
    }

    /**
     * Perbarui berita.
     */
    public function update(NewsRequest $request, News $news): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['title']) && array_key_exists('slug', $data) && empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $news->news_id);
        }

        $news->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil diperbarui.',
            'data' => $news->fresh(['galleries']),
        ]);
    }

    /**
     * Hapus berita.
     */
    public function destroy(News $news): JsonResponse
    {
        $news->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil dihapus.',
        ]);
    }

    /**
     * Generate slug unik dari judul berita.
     */
    protected function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title) ?: 'berita';
        $slug = $baseSlug;
        $counter = 1;

        while (
            News::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('news_id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
