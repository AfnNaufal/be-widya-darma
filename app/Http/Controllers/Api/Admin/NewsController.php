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
     * Daftar seluruh berita / galeri kegiatan (bisa filter ?category= seperti Ekstrakurikuler, Berita, Prestasi, dll.).
     */
    public function index(Request $request): JsonResponse
    {
        $query = News::with(['admin:admin_id,name', 'galleries' => fn ($q) => $q->orderBy('sort_order')])
            ->withCount('visits');

        if ($request->filled('category') && strtolower((string) $request->query('category')) !== 'semua') {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $news = $query->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $news,
        ]);
    }

    /**
     * Buat berita / kegiatan baru (slug otomatis, gambar & photos dikonversi ke WebP).
     */
    public function store(NewsRequest $request): JsonResponse
    {
        $data = $request->validatedWithWebp('news');
        $photos = $data['photos'] ?? null;
        unset($data['photos']);

        if (empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
        }

        if (empty($data['category'])) {
            $data['category'] = 'Berita';
        }

        if (empty($data['status'])) {
            $data['status'] = 'Terbit';
        }

        if (! array_key_exists('published_at', $data)) {
            $data['published_at'] = in_array($data['status'], ['Terbit', 'published'], true) ? now() : null;
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
            'message' => 'Berita / kegiatan berhasil ditambahkan.',
            'data' => $news->load(['galleries' => fn ($q) => $q->orderBy('sort_order')]),
        ], 201);
    }

    /**
     * Detail berita / kegiatan berdasarkan news_id.
     */
    public function show(News $news): JsonResponse
    {
        $news->load(['admin:admin_id,name', 'galleries' => fn ($q) => $q->orderBy('sort_order')])->loadCount('visits');

        return response()->json([
            'success' => true,
            'data' => $news,
        ]);
    }

    /**
     * Perbarui berita / kegiatan.
     */
    public function update(NewsRequest $request, News $news): JsonResponse
    {
        $data = $request->validatedWithWebp('news');
        $photos = $data['photos'] ?? null;
        unset($data['photos']);

        if (isset($data['title']) && array_key_exists('slug', $data) && empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['title'], $news->news_id);
        }

        if (! array_key_exists('published_at', $data) && isset($data['status'])) {
            $data['published_at'] = in_array($data['status'], ['Terbit', 'published'], true) ? ($news->published_at ?? now()) : null;
        }

        $processedPhotos = $this->processPhotosArray($photos);

        if (empty($data['image_url']) && ! empty($processedPhotos)) {
            $data['image_url'] = $processedPhotos[0]['image_url'];
        }

        $news->update($data);

        if ($photos !== null) {
            foreach ($news->galleries as $oldGallery) {
                $oldGallery->delete();
            }
            $this->syncNewsGalleries($news, $processedPhotos);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berita / kegiatan berhasil diperbarui.',
            'data' => $news->fresh(['galleries' => fn ($q) => $q->orderBy('sort_order')]),
        ]);
    }

    /**
     * Hapus berita / kegiatan (beserta file .webp di galeri terkait).
     */
    public function destroy(News $news): JsonResponse
    {
        foreach ($news->galleries as $gallery) {
            $gallery->delete();
        }

        $news->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berita / kegiatan berhasil dihapus.',
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
     * @return array<int, array{image_url: string, caption: ?string, sort_order: int, is_cover: bool}>
     */
    protected function processPhotosArray(?array $photos): array
    {
        if (empty($photos)) {
            return [];
        }

        $result = [];
        foreach (array_values($photos) as $index => $photo) {
            $rawUrl = $photo['url'] ?? null;
            if (empty($rawUrl)) {
                continue;
            }
            $webpPath = $this->imageUploadService->uploadAndConvertToWebp($rawUrl, 'gallery');
            if ($webpPath) {
                $result[] = [
                    'image_url' => $webpPath,
                    'caption' => $photo['caption'] ?? null,
                    'sort_order' => (int) ($photo['order'] ?? ($index + 1)),
                    'is_cover' => $index === 0,
                ];
            }
        }

        return $result;
    }

    /**
     * Menyimpan foto-foto berita ke tabel gallery.
     *
     * @param  array<int, array{image_url: string, caption: ?string, sort_order: int, is_cover: bool}>  $processedPhotos
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
                'sort_order' => $photo['sort_order'],
                'is_cover' => $photo['is_cover'],
                'created_at' => now(),
            ]);
        }
    }
}
