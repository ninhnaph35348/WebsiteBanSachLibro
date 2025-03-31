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
    // Lấy thông tin cá nhân user
    public function profile(Request $request)
    {
        // Lấy thông tin user từ token
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'message' => 'Lấy thông tin thành công',
            'user' => $user
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        // Lấy thông tin user từ token
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate dữ liệu
        $validator = Validator::make($request->all(), [
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'fullname' => 'nullable|string|max:255',
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'phone' => 'nullable|string|min:10|max:15' . $user->id,
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        // Cập nhật thông tin user
        $data = [
            'fullname' => $request->fullname ?? $user->fullname,
            'email' => $request->email ?? $user->email,
            'phone' => $request->phone ?? $user->phone,
            'address' => $request->address ?? $user->address,
            'birth_date' => $request->birth_date ?? $user->birth_date,
        ];

        // Xử lý avatar nếu có tải lên
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        $user->update($data);

        return response()->json([
            'message' => 'Cập nhật thông tin thành công',
            'user' => $user
        ], 200);
    }


    // Đổi mật khẩu
    public function changePassword(Request $request)
    {
        // Lấy user từ token
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:6|same:confirm_new_password',
            'confirm_new_password' => 'required|string|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        // Kiểm tra mật khẩu cũ có đúng không
        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json(['message' => 'Mật khẩu cũ không chính xác'], 401);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Đổi mật khẩu thành công'], 200);
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
