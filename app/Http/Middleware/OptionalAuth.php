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
                // Kiểm tra xem token có hết hạn không
                if ($accessToken->expires_at && $accessToken->expires_at->isPast()) {
                    // Token đã hết hạn
                    return response()->json(['message' => 'Token đã hết hạn'], 401);
                }

                // Kiểm tra xem token có được sử dụng bởi người dùng hợp lệ không
                if (!$accessToken->tokenable) {
                    return response()->json(['message' => 'User không hợp lệ'], 401);
                }

                // Gán user vào request (tương tự auth()->user())
                $request->setUserResolver(fn() => $accessToken->tokenable);
            } else {
                // Token không hợp lệ
                return response()->json(['message' => 'Token không hợp lệ'], 401);
            }
        }

        return $next($request);
    }
}
