<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
//
class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $categories = Category::where('del_flg', 0)->paginate(10);
        $categories = Category::all();
        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $category = Category::create($request->only('name', 'description'));

        return response()->json([
            'message' => 'Thêm mới danh mục thành công',
            'category' => $category
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::find($id);

        // dd($category);

        if (!$category) {
            return response()->json(['message' => "Không tìm thấy danh mục"], 404);
        }

        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $category = Category::find($id);


        if (!$category) {
            return response()->json(['message' => "Không tìm thấy danh mục"], 404);
        }


        // dd($request->all());
        $category->update($request->only('name', 'description'));

        return response()->json([
            'message' => ' Cập nhật danh mục thành công',
            'category' => $category
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $category = Category::find($id);


        if (!$category) {
            return response()->json(['message' => "Không tìm thấy danh mục"], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Xóa danh mục thành công']);
    }
}
