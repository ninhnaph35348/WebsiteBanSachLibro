<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class OptionalAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Lấy token từ header Authorization
        $token = $request->bearerToken();

        if ($token) {
            // Tìm token trong bảng personal_access_tokens
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                // Gán user vào request (tương tự auth()->user())
                $request->setUserResolver(fn () => $accessToken->tokenable);
            }
        }

        return $next($request);
    }
}
