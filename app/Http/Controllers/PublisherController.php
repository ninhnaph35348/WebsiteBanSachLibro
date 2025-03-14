<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $publisher = Publisher::where('del_flg', 0)->get(); // Lấy dữ liệu chưa bị xóa
        return response()->json($publisher, 200);
    }
    

    /**publisher
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $publisher = Publisher::create($request->only('name'));

        return response()->json([
            'message' => 'Thêm mới nhà xuất bản thành công',
            'publisher' => $publisher
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $publisher = Publisher::find($id);

        // dd($publisher);

        if (!$publisher) {
            return response()->json(['message' => "Không tìm thấy tên nhà xuất bản"], 404);
        }

        return response()->json($publisher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $publisher = Publisher::find($id);


        if (!$publisher) {
            return response()->json(['message' => "Không tìm thấy tên nhà xuất bản"], 404);
        }


        // dd($request->all());
        $publisher->update($request->only('name'));

        return response()->json([
            'message' => ' Cập nhật tên nhà xuất bản thành công',
            'publisher' => $publisher
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $publisher = Publisher::find($id);
    
        if (!$publisher) {
            return response()->json(['message' => 'Không tìm thấy nhà xuất bản'], 404);
        }
    
        $publisher->update(['del_flg' => 1]); // Xóa mềm bằng cách đặt del_flg = 1
    
        return response()->json(['message' => 'Nhà xuất bản đã bị ẩn'], 200);
    }
    
}
