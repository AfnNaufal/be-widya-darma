<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolProfile;
use Illuminate\Http\JsonResponse;

class SchoolProfileController extends Controller
{
    /**
     * Mengambil profil sekolah untuk halaman publik.
     */
    public function show(): JsonResponse
    {
        $profile = SchoolProfile::first();

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }
}
