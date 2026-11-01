<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Spmb;
use Illuminate\Http\JsonResponse;

class SpmbController extends Controller
{
    /**
     * Mengambil daftar informasi SPMB yang aktif untuk publik.
     */
    public function index(): JsonResponse
    {
        $spmb = Spmb::where('is_active', true)
            ->orderByDesc('start_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $spmb,
        ]);
    }

    /**
     * Detail informasi SPMB.
     */
    public function show(Spmb $spmb): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $spmb,
        ]);
    }
}
