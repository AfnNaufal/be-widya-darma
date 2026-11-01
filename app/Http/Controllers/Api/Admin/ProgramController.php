<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProgramRequest;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Daftar seluruh program sekolah.
     */
    public function index(Request $request): JsonResponse
    {
        $programs = Program::with('admin:admin_id,name')
            ->orderByDesc('created_at')
            ->paginate($request->integer('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $programs,
        ]);
    }

    /**
     * Tambah program sekolah baru.
     */
    public function store(ProgramRequest $request): JsonResponse
    {
        $program = Program::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Program sekolah berhasil ditambahkan.',
            'data' => $program,
        ], 201);
    }

    /**
     * Detail satu program sekolah.
     */
    public function show(Program $program): JsonResponse
    {
        $program->load('admin:admin_id,name');

        return response()->json([
            'success' => true,
            'data' => $program,
        ]);
    }

    /**
     * Perbarui data program sekolah.
     */
    public function update(ProgramRequest $request, Program $program): JsonResponse
    {
        $program->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Program sekolah berhasil diperbarui.',
            'data' => $program->fresh(),
        ]);
    }

    /**
     * Hapus program sekolah.
     */
    public function destroy(Program $program): JsonResponse
    {
        $program->delete();

        return response()->json([
            'success' => true,
            'message' => 'Program sekolah berhasil dihapus.',
        ]);
    }
}
