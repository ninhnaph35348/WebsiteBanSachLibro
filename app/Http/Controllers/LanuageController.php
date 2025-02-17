<?php

namespace App\Http\Controllers;

use App\Models\Lanuage;
use Illuminate\Http\Request;

class LanuageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lanuages = Lanuage::all();
        return response()->json($lanuages);
    }

    /**lanuage
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $lanuage = Lanuage::create($request->only('name'));

        return response()->json([
            'message' => 'Thêm mới ngôn ngữ thành công',
            'lanuage' => $lanuage
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $lanuage = Lanuage::find($id);

        // dd($lanuage);

        if (!$lanuage) {
            return response()->json(['message' => "Không tìm thấy ngôn ngữ"], 404);
        }

        return response()->json($lanuage);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $lanuage = Lanuage::find($id);


        if (!$lanuage) {
            return response()->json(['message' => "Không tìm thấy ngôn ngữ"], 404);
        }


        // dd($request->all());
        $lanuage->update($request->only('name'));

        return response()->json([
            'message' => ' Cập nhật ngôn ngữ thành công',
            'lanuage' => $lanuage
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $lanuage = Lanuage::find($id);


        if (!$lanuage) {
            return response()->json(['message' => "Không tìm thấy ngôn ngữ"], 404);
        }

        $lanuage->delete();

        return response()->json(['message' => 'Xóa ngôn ngữ thành công']);
    }
}
