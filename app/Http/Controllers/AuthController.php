<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
//
class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users', //username không được trùng vì có UNIQUE KEY
            'fullname' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6|same:confirm_password',
            'confirm_password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        $user = User::create([
            'username' => $request->username,
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'client',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng ký thành công',
            'user' => $user,
            'token' => $token
        ], 201);
    }


    // Đăng nhập
    public function login_()
    {
        return response()->json(['message' => 'Xin vui lòng đăng nhập'], 401);
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email hoặc mật khẩu không chính xác'], 401);
        }

        // Kiểm tra nếu tài khoản bị khóa
        if ($user->status === 'inactive') {
            return response()->json([
                'message' => 'Tài khoản của bạn đã bị khóa do nhập sai quá nhiều lần.',
                'status' => 'inactive'
            ], 403);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            $user->failed_attempts += 1;

            // Khóa tài khoản nếu sai quá 5 lần
            if ($user->failed_attempts >= 5) {
                $user->status = 'inactive';
                $user->save();

                // Ghi log khóa tài khoản
                DB::table('logs')->insert([
                    'message' => "Tài khoản {$user->email} bị khóa do nhập sai quá nhiều lần",
                    'created_at' => now(),
                ]);

                return response()->json([
                    'message' => 'Tài khoản của bạn đã bị khóa do nhập sai quá nhiều lần.',
                    'status' => 'inactive',
                    'failed_attempts' => $user->failed_attempts
                ], 403);
            }

            $user->save();
            return response()->json([
                'message' => 'Email hoặc mật khẩu không chính xác',
                'failed_attempts' => $user->failed_attempts
            ], 401);
        }

        // Reset lại failed_attempts nếu đăng nhập thành công
        $user->failed_attempts = 0;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công',
            'user' => $user,
            'role' => $user->role,
            'token' => $token
        ], 200);
    }




    // Đăng xuất
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Đăng xuất thành công'], 200);
        }
        return response()->json(['message' => 'Bạn chưa đăng nhập'], 401);
    }
}
