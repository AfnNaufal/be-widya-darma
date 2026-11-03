<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Models\ActivityLog;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const TOKEN_LIFETIME_HOURS = 12;

    /**
     * Login admin dan terbitkan token baru (token lama otomatis tidak berlaku).
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $admin = Admin::where('email', $request->validated('email'))->first();

        if (! $admin || ! Hash::check($request->validated('password'), $admin->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $plainToken = Str::random(64);

        $admin->forceFill([
            'api_token' => hash('sha256', $plainToken),
            'api_token_expires_at' => now()->addHours(self::TOKEN_LIFETIME_HOURS),
        ])->save();

        $this->recordAuthActivity($admin, 'login', $request);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $plainToken,
                'token_type' => 'Bearer',
                'expires_at' => $admin->api_token_expires_at,
                'admin' => $admin,
            ],
        ]);
    }

    /**
     * Logout: cabut token yang sedang dipakai.
     */
    public function logout(Request $request): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();

        $this->recordAuthActivity($admin, 'logout', $request);

        $admin->forceFill([
            'api_token' => null,
            'api_token_expires_at' => null,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    /**
     * Data admin yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    protected function recordAuthActivity(Admin $admin, string $action, Request $request): void
    {
        ActivityLog::create([
            'admin_id' => $admin->admin_id,
            'action' => $action,
            'target_table' => $admin->getTable(),
            'target_id' => $admin->admin_id,
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
    }
}
