<?php

namespace App\Http\Controllers;

use App\Models\Cover;
use Illuminate\Http\Request;
//
class CoverController extends Controller
{
    public function index()
    {
        $covers = Cover::where('del_flg', 0)->get();

        return response()->json($covers);
    }

    public function store(Request $request)
    {
        $cover = Cover::create($request->only('name'));

        return response()->json([
            'message' => 'Thêm biến thể thành công',
            'cover' => $cover
        ]);
    }

    public function show($id)
    {
        $cover = Cover::find($id);

        if (!$cover) {
            return response()->json(['message' => "Không tìm thấy biến thể"], 404);
        }

        return response()->json($cover);
    }

    public function update(Request $request, $id)
    {
        $cover = Cover::find($id);


        if (!$cover) {
            return response()->json(['message' => "Không tìm thấy biến thể"], 404);
        }

        $cover->update($request->only('name'));

        return response()->json([
            'message' => ' Cập nhật biến thể thành công',
            'cover' => $cover
        ]);
    }

    public function destroy($id)
    {
        $cover = cover::find($id);


        if (!$cover) {
            return response()->json(['message' => "Không tìm thấy biến thể"], 404);
        }

        $cover->update(['del_flg' => 1]);

        return response()->json(['message' => 'Đã ẩn biến thể'], 200);
    }
}
