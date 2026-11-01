<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GalleryRequest;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Daftar seluruh foto galeri.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Gallery::with(['admin:admin_id,name', 'news:news_id,title,slug'])
            ->orderByDesc('created_at');

        if ($request->filled('news_id')) {
            $query->where('news_id', $request->integer('news_id'));
        }

        $galleries = $query->paginate($request->integer('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $galleries,
        ]);
    }

    /**
     * Unggah item galeri baru.
     */
    public function store(GalleryRequest $request): JsonResponse
    {
        $gallery = Gallery::create([
            ...$request->validated(),
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Foto galeri berhasil ditambahkan.',
            'data' => $gallery,
        ], 201);
    }

    /**
     * Detail item galeri.
     */
    public function show(Gallery $gallery): JsonResponse
    {
        $gallery->load(['admin:admin_id,name', 'news:news_id,title,slug']);

        return response()->json([
            'success' => true,
            'data' => $gallery,
        ]);
    }

    /**
     * Perbarui item galeri.
     */
    public function update(GalleryRequest $request, Gallery $gallery): JsonResponse
    {
        $gallery->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Foto galeri berhasil diperbarui.',
            'data' => $gallery->fresh(),
        ]);
    }

    /**
     * Hapus item galeri.
     */
    public function destroy(Gallery $gallery): JsonResponse
    {
        $gallery->delete();

        return response()->json([
            'success' => true,
            'message' => 'Foto galeri berhasil dihapus.',
        ]);
    }
}
