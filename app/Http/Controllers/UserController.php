<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Thêm mới một người dùng.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:150|unique:users',
            'fullname' => 'required|string|max:150',
            'email' => 'required|string|email|max:150|unique:users',
            'password' => 'required|string|min:6',
            'user_type' => 'required|in:admin,client',
            'status' => 'required|in:active,inactive',
            'role' => 'required|integer|min:0|max:1',
        ]);

        $user = User::create([
            'username' => $request->username,
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'avatar' => $request->avatar ?? null,
            'phone' => $request->phone ?? null,
            'address' => $request->address ?? null,
            'birth_date' => $request->birth_date ?? null,
            'user_type' => $request->user_type,
            'status' => $request->status,
            'role' => $request->role,
        ]);

        return response()->json([
            'message' => 'Thêm mới người dùng thành công',
            'user' => $user
        ]);
    }

    /**
     * Hiển thị chi tiết một người dùng.
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => "Không tìm thấy người dùng"], 404);
        }

        return response()->json($user);
    }

    /**
     * Cập nhật thông tin người dùng.
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => "Không tìm thấy người dùng"], 404);
        }

        $request->validate([
            'username' => 'string|max:150|unique:users,username,' . $id,
            'fullname' => 'string|max:150',
            'email' => 'string|email|max:150|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6',
            'user_type' => 'in:admin,client',
            'status' => 'in:active,inactive',
            'role' => 'integer|min:0|max:1',
        ]);

        $user->update([
            'username' => $request->username ?? $user->username,
            'fullname' => $request->fullname ?? $user->fullname,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'avatar' => $request->avatar ?? $user->avatar,
            'phone' => $request->phone ?? $user->phone,
            'address' => $request->address ?? $user->address,
            'birth_date' => $request->birth_date ?? $user->birth_date,
            'user_type' => $request->user_type ?? $user->user_type,
            'status' => $request->status ?? $user->status,
            'role' => $request->role ?? $user->role,
        ]);

        return response()->json([
            'message' => 'Cập nhật người dùng thành công',
            'user' => $user
        ]);
    }

    /**
     * Xóa một người dùng.
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => "Không tìm thấy người dùng"], 404);
        }

        $user->delete();

        return response()->json(['message' => 'Xóa người dùng thành công']);
    }
}
