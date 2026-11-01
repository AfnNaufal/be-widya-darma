<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExtracurricularRequest;
use App\Models\Extracurricular;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtracurricularController extends Controller
{
    /**
     * Daftar seluruh ekstrakurikuler.
     */
    public function index(Request $request): JsonResponse
    {
        $extracurriculars = Extracurricular::with('admin:admin_id,name')
            ->orderBy('name')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $extracurriculars,
        ]);
    }

    /**
     * Tambah ekstrakurikuler baru.
     */
    public function store(ExtracurricularRequest $request): JsonResponse
    {
        $extracurricular = Extracurricular::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ekstrakurikuler berhasil ditambahkan.',
            'data' => $extracurricular,
        ], 201);
    }

    /**
     * Detail ekstrakurikuler.
     */
    public function show(Extracurricular $extracurricular): JsonResponse
    {
        $extracurricular->load('admin:admin_id,name');

        return response()->json([
            'success' => true,
            'data' => $extracurricular,
        ]);
    }

    /**
     * Perbarui ekstrakurikuler.
     */
    public function update(ExtracurricularRequest $request, Extracurricular $extracurricular): JsonResponse
    {
        $extracurricular->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ekstrakurikuler berhasil diperbarui.',
            'data' => $extracurricular->fresh(),
        ]);
    }

    /**
     * Hapus ekstrakurikuler.
     */
    public function destroy(Extracurricular $extracurricular): JsonResponse
    {
        $extracurricular->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ekstrakurikuler berhasil dihapus.',
        ]);
    }
}
