<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Extracurricular;
use Illuminate\Http\JsonResponse;

class ExtracurricularController extends Controller
{
    /**
     * Mengambil daftar ekstrakurikuler untuk publik.
     */
    public function index(): JsonResponse
    {
        $extracurriculars = Extracurricular::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $extracurriculars,
        ]);
    }

    /**
     * Detail satu ekstrakurikuler.
     */
    public function show(Extracurricular $extracurricular): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $extracurricular,
        ]);
    }
}
