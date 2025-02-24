<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // Danh sách user
    public function index()
    {
        return response()->json(User::all(), 200);
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
        // dd($request->all());
        // $request->validate([
        //     'username' => 'required', //username tên khác ko trùng vs bảng users
        //     'fullname' => 'required',
        //     'email' => 'required', // email tên khác ko trùng
        //     'password' => 'required|min:6',
        //     'role' => 'required',
        // ]);
        $user = User::create($request->all());

        return response()->json([
            'message' => 'Thêm mới thành công',
            'user' => $user
        ]);
    }

    // Cập nhật user
    public function update(Request $request, $id)
    {
    //     $request->validate([
    //         'username' => 'required|unique:users',
    //         'fullname' => 'required',
    //         'email' => 'required|email|unique:users',
    //         'password' => 'required|min:6',
    //         'role' => 'required|in:0,1',
    //     ]);
        
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User không tồn tại'], 404);
        }

        $user->update($request->all());
        return response()->json([
            'message' => 'Sửa thành công',
            'user' => $user
        ]);
    }

    // Xóa user
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User không tồn tại'], 404);
        }

        $user->delete();
        return response()->json(['message' => 'User đã bị xóa'], 200);
    }
}
