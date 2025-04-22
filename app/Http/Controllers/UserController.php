<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $userType = $request->query('role');

        $query = User::where('del_flg', 0);

        if ($userType) {
            $query->where('role', $userType);
        }

        $users = $query->orderBy('id', 'desc')->get();

        return response()->json($users, 200);
    }



    // Lấy thông tin user theo ID
    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User không tồn tại'], 404);
        }
        return response()->json($user, 200);
    }

    // Tạo user mới
    public function store(Request $request)
    {
        try {
            $val = $request->validate([
                'username' => 'required|string|max:255',
                'fullname' => 'required|string|max:255',
                'email' => 'required',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'password' => 'required|string|min:6',
                'phone' => 'nullable|regex:/^[0-9]+$/|max:20',
                'address' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date',
                'status' => 'required|string',
                'role' => 'required|string',
            ]);
            //
            if (User::where('username', $request->username)->exists()) {
                return response()->json(['message' => 'Username đã tồn tại'], 400);
            }

            if (User::where('email', $request->email)->exists()) {
                return response()->json(['message' => 'Email đã tồn tại'], 400);
            }

            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('uploads/user', 'public');

                $val['avatar'] = $avatarPath; // Lưu đường dẫn đúng vào database

            }
            $user = User::create($val);

            return response()->json([
                'message' => 'Thêm mới thành công',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi tạo user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // Cập nhật user
    public function update(Request $request, $id)
    {
        try {
            $val = $request->validate([
                'username' => 'string|max:255',
                'fullname' => 'string|max:255',
                'email' => 'email',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'password' => 'string|min:6',
                'phone' => 'nullable|regex:/^[0-9]+$/|max:20',
                'address' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date',
                'status' => 'string',
                'role' => 'string',
            ]);

            $user = User::find($id);
            if (!$user) {
                return response()->json(['message' => 'User không tồn tại'], 404);
            }
            // Kiểm tra username trùng nhưng bỏ qua user hiện tại
            if (User::where('username', $request->username)->where('id', '!=', $id)->exists()) {
                return response()->json(['message' => 'Username đã tồn tại'], 400);
            }

            // Kiểm tra email trùng nhưng bỏ qua user hiện tại
            if (User::where('email', $request->email)->where('id', '!=', $id)->exists()) {
                return response()->json(['message' => 'Email đã tồn tại'], 400);
            }

            // ✅ Xử lý ảnh đại diện (avatar)
            if ($request->hasFile('avatar')) {
                // Xóa avatar cũ nếu có
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                // Lưu avatar mới
                $val['avatar'] = $request->file('avatar')->store('uploads/user', 'public');
            }

            // ✅ Nếu status được cập nhật thành 'active', reset failed_attempts
            if (isset($val['status']) && $val['status'] === 'active') {
                $user->failed_attempts = 0;
            }
            $user->update($val);

            // ✅ Cập nhật thông tin sản phẩm
            $user->update($val);

            // $user->update($request->all());
            return response()->json([
                'message' => 'Sửa thành công',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi sửa user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateMe(Request $request)
    {
        try {
            $user = $request->user(); // Lấy user từ token
            if (!$user) {
                return response()->json(['message' => 'Người dùng không tồn tại'], 404);
            }

            $val = $request->validate([
                'fullname' => 'string|max:255',
                'phone' => 'nullable|regex:/^[0-9]+$/|max:20',
                'address' => 'nullable|string|max:255',
                'birth_date' => 'nullable|date',
                'status' => 'string',
                'role' => 'string',
            ]);

            $user->update($val);

            return response()->json([
                'message' => 'Cập nhật thành công',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi cập nhật thông tin',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Xóa user
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User không tồn tại'], 404);
        }

        $user->update(['del_flg' => 1]); // Cập nhật del_flg = 1 thay vì xóa

        return response()->json(['message' => 'User đã bị ẩn'], 200);
    }
}
