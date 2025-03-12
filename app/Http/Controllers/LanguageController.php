<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $language = Language::where('del_flg', 0)->get(); 
        return response()->json($language, 200);
    }
    

    /**language
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $language = Language::create($request->only('name'));

        return response()->json([
            'message' => 'Thêm mới ngôn ngữ thành công',
            'language' => $language
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $language = Language::find($id);

        // dd($language);

        if (!$language) {
            return response()->json(['message' => "Không tìm thấy ngôn ngữ"], 404);
        }

        return response()->json($language);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $language = Language::find($id);


        if (!$language) {
            return response()->json(['message' => "Không tìm thấy ngôn ngữ"], 404);
        }


        // dd($request->all());
        $language->update($request->only('name'));

        return response()->json([
            'message' => ' Cập nhật ngôn ngữ thành công',
            'language' => $language
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $language = Language::find($id);
    
        if (!$language) {
            return response()->json(['message' => 'Không tìm thấy ngôn ngữ'], 404);
        }
    
        $language->update(['del_flg' => 1]); // Xóa mềm bằng cách đặt del_flg = 1
    
        return response()->json(['message' => 'Ngôn ngữ đã bị ẩn'], 200);
    }
    
}
