<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SchoolProfileRequest;
use App\Models\SchoolProfile;
use Illuminate\Http\JsonResponse;

class SchoolProfileController extends Controller
{
    /**
     * Tampilkan profil sekolah saat ini.
     */
    public function show(): JsonResponse
    {
        $profile = SchoolProfile::with('admin:admin_id,name,email')->first();

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    /**
     * Buat atau perbarui profil sekolah (Single Record / Upsert).
     */
    public function upsert(SchoolProfileRequest $request): JsonResponse
    {
        $profile = SchoolProfile::first();

        if ($profile) {
            $profile->update($request->validated());
            $message = 'Profil sekolah berhasil diperbarui.';
            $status = 200;
        } else {
            $profile = SchoolProfile::create($request->validated());
            $message = 'Profil sekolah berhasil dibuat.';
            $status = 201;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $profile->fresh(),
        ], $status);
    }
}
