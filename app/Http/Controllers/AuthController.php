<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login_(){
        return response()->json(['message' => 'Xin vui lòng đăng nhập'],401);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
    
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Email hoặc mật khẩu không chính xác'], 401);
        }
    
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'role' => $this->getRoleName($user->role),
            'token' => $token
        ], 200);
    }
    
    private function getRoleName($role)
    {
        return match ($role) {
            0 => 'Super Admin',
            1 => 'Admin',
            default => 'Client'
        };
    }
    

    /**
     * Đăng xuất người dùng.
     */
    public function logout(Request $request)
{
    if ($request->user()) {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đăng xuất thành công'], 200);
    }
    return response()->json(['message' => 'Bạn chưa đăng nhập'], 401);
}
}
