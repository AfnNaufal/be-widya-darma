<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class UploadController extends Controller
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    /**
     * Mengunggah gambar (via multipart file 'image'/'file' atau Base64 string) dan mengompresnya menjadi .webp.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'folder' => ['nullable', 'string', 'max:50', 'alpha_dash'],
            'image' => ['nullable'],
            'file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $folder = (string) $request->input('folder', 'uploads');
        $input = $request->file('image') ?? $request->file('file') ?? $request->input('image');

        if ($input instanceof UploadedFile) {
            $request->validate([
                'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            ]);
        }

        if (empty($input)) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih file gambar atau kirimkan data gambar terlebih dahulu.',
            ], 422);
        }

        try {
            $path = $this->imageUploadService->uploadAndConvertToWebp($input, $folder);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Gambar berhasil diunggah dan dikompres ke format WebP.',
            'data' => [
                'path' => $path,
                'url' => url((string) $path),
                'image_url' => $path,
            ],
        ], 201);
    }
}
