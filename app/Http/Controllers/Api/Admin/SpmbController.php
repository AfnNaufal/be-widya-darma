<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpmbRequest;
use App\Models\Spmb;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpmbController extends Controller
{
    /**
     * Daftar seluruh gelombang/informasi SPMB.
     */
    public function index(Request $request): JsonResponse
    {
        $spmbList = Spmb::with('admin:admin_id,name')
            ->orderByDesc('start_date')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $spmbList,
        ]);
    }

    /**
     * Tambah informasi SPMB baru.
     */
    public function store(SpmbRequest $request): JsonResponse
    {
        $spmb = Spmb::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Informasi SPMB berhasil ditambahkan.',
            'data' => $spmb,
        ], 201);
    }

    /**
     * Detail informasi SPMB.
     */
    public function show(Spmb $spmb): JsonResponse
    {
        $spmb->load('admin:admin_id,name');

        return response()->json([
            'success' => true,
            'data' => $spmb,
        ]);
    }

    /**
     * Perbarui informasi SPMB.
     */
    public function update(SpmbRequest $request, Spmb $spmb): JsonResponse
    {
        $spmb->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Informasi SPMB berhasil diperbarui.',
            'data' => $spmb->fresh(),
        ]);
    }

    /**
     * Hapus informasi SPMB.
     */
    public function destroy(Spmb $spmb): JsonResponse
    {
        $spmb->delete();

        return response()->json([
            'success' => true,
            'message' => 'Informasi SPMB berhasil dihapus.',
        ]);
    }
}
