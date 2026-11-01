<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\JsonResponse;

class ProgramController extends Controller
{
    /**
     * Mengambil daftar program sekolah untuk publik.
     */
    public function index(): JsonResponse
    {
        $programs = Program::orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data' => $programs,
        ]);
    }

    /**
     * Detail satu program sekolah.
     */
    public function show(Program $program): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $program,
        ]);
    }
}
