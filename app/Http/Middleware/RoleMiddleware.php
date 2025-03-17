<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Bạn chưa đăng nhập!'], 401);
        }

        // Tách từng role trong middleware (hỗ trợ "s.admin|admin")
        $roles = explode('|', implode('|', $roles));

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập API này!',
                'required_roles' => $roles,
                'your_role' => $user->role
            ], 403);
        }

        return $next($request);
    }
}
