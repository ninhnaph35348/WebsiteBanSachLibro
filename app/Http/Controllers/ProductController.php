<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
      /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    /**product
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $product = Product::create($request->all());

        return response()->json([
            'message' => 'Thêm mới tác giả thành công',
            'product' => $product
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = Product::find($id);

        // dd($product);

        if (!$product) {
            return response()->json(['message' => "Không tìm thấy tên tác giả"], 404);
        }

        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);


        if (!$product) {
            return response()->json(['message' => "Không tìm thấy tên tác giả"], 404);
        }


        // dd($request->all());
        $product->update($request->only('name'));

        return response()->json([
            'message' => ' Cập nhật tên tác giả thành công',
            'product' => $product
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);


        if (!$product) {
            return response()->json(['message' => "Không tìm thấy tên tác giả"], 404);
        }

        $product->delete();

        return response()->json(['message' => 'Xóa tác giả thành công']);
    }
}
