<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        $users = $query->get();

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
            $request->validate([
                'username' => 'required|string|max:255',
                'fullname' => 'required|string|max:255',
                'email' => 'required',
                'password' => 'required|string|min:6',
                'phone' => 'string|max:20',
                'address' => 'string',
                'birth_date' => 'date',
                'status' => 'required|string',
                'role' => 'required|string',
            ]);
            if (User::where('username', $request->username)->exists()) {
                return response()->json(['message' => 'Username đã tồn tại'], 400);
            }

            if (User::where('email', $request->email)->exists()) {
                return response()->json(['message' => 'Email đã tồn tại'], 400);
            }
            $user = User::create($request->all());

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
            $request->validate([
                'username' => 'required|string|max:255',
                'fullname' => 'required|string|max:255',
                'email' => 'required',
                'password' => 'required|string|min:6',
                'phone' => 'string|max:20',
                'address' => 'string',
                'birth_date' => 'date',
                'status' => 'required|string',
                'role' => 'required|string',
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

            $user->update($request->all());
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
