<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * Mengambil daftar direktori guru & staf untuk halaman publik (/guru).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Teacher::orderBy('sort_order')->orderBy('name');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Detail profil satu guru.
     */
    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $teacher,
        ]);
    }
}
