<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    /**
     * Mengambil daftar galeri foto (bisa difilter berdasarkan news_id jika ada).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Gallery::with('news:news_id,title,slug')
            ->orderByDesc('created_at');

        if ($request->has('news_id')) {
            $query->where('news_id', $request->query('news_id'));
        }

        $galleries = $query->paginate($request->input('per_page', 12));

        return response()->json([
            'success' => true,
            'data' => $galleries,
        ]);
    }
}
