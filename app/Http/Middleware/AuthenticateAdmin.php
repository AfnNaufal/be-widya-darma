<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdmin
{
    /**
     * Autentikasi admin lewat Bearer token (disimpan sebagai hash SHA-256).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        $admin = $plainToken
            ? Admin::where('api_token', hash('sha256', $plainToken))
                ->where('api_token_expires_at', '>', now())
                ->first()
            : null;

        if (! $admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Token tidak valid atau sudah kedaluwarsa.',
            ], 401);
        }

        $request->setUserResolver(fn () => $admin);

        return $next($request);
    }
}
