<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NewsRequest;
use App\Models\Gallery;
use App\Models\News;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

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
     * Buat berita baru (slug otomatis di-generate dari title jika dikosongkan, gambar & photos dikonversi ke WebP).
     */
    public function store(NewsRequest $request): JsonResponse
    {
        $data = $request->validatedWithWebp('news');
        $photos = $data['photos'] ?? null;
        $status = $data['status'] ?? null;
        unset($data['photos'], $data['status']);

        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        if (! array_key_exists('published_at', $data) && $status !== null) {
            $data['published_at'] = in_array($status, ['Terbit', 'published'], true) ? now() : null;
        }

        $processedPhotos = $this->processPhotosArray($photos);

        if (empty($data['image_url']) && ! empty($processedPhotos)) {
            $data['image_url'] = $processedPhotos[0]['image_url'];
        }

        $news = News::create($data);

        if (! empty($processedPhotos)) {
            $this->syncNewsGalleries($news, $processedPhotos);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berita berhasil ditambahkan.',
            'data' => $news->load('galleries'),
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
        $data = $request->validatedWithWebp('news');
        $photos = $data['photos'] ?? null;
        $status = $data['status'] ?? null;
        unset($data['photos'], $data['status']);

        if (isset($data['title']) && array_key_exists('slug', $data) && empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $news->news_id);
        }

        if (! array_key_exists('published_at', $data) && $status !== null) {
            $data['published_at'] = in_array($status, ['Terbit', 'published'], true) ? ($news->published_at ?? now()) : null;
        }

        $processedPhotos = $this->processPhotosArray($photos);

        if (empty($data['image_url']) && ! empty($processedPhotos)) {
            $data['image_url'] = $processedPhotos[0]['image_url'];
        }

        $news->update($data);

        if ($photos !== null) {
            $news->galleries()->delete();
            $this->syncNewsGalleries($news, $processedPhotos);
        }

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

    /**
     * Mengonversi setiap foto di array photos menjadi .webp.
     *
     * @param  array<int, array<string, mixed>>|null  $photos
     * @return array<int, array{image_url: string, caption: ?string}>
     */
    protected function processPhotosArray(?array $photos): array
    {
        if (empty($photos)) {
            return [];
        }

        $result = [];
        foreach ($photos as $photo) {
            $rawUrl = $photo['url'] ?? null;
            if (empty($rawUrl)) {
                continue;
            }
            $webpPath = $this->imageUploadService->uploadAndConvertToWebp($rawUrl, 'gallery');
            if ($webpPath) {
                $result[] = [
                    'image_url' => $webpPath,
                    'caption' => $photo['caption'] ?? null,
                ];
            }
        }

        return $result;
    }

    /**
     * Menyimpan foto-foto berita ke tabel gallery.
     *
     * @param  array<int, array{image_url: string, caption: ?string}>  $processedPhotos
     */
    protected function syncNewsGalleries(News $news, array $processedPhotos): void
    {
        foreach ($processedPhotos as $photo) {
            Gallery::create([
                'admin_id' => $news->admin_id,
                'news_id' => $news->news_id,
                'title' => $photo['caption'] ?: $news->title,
                'image_url' => $photo['image_url'],
                'caption' => $photo['caption'],
                'created_at' => now(),
            ]);
        }
    }
}
