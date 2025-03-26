<?php

namespace App\Http\Controllers;

use App\Http\Resources\VariantResoure;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = ProductVariant::with(['product', 'cover'])
            ->where('del_flg', 0)
            ->paginate(10);
        return VariantResoure::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'cover_id' => 'required',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|min:0',
            'promotion' => 'nullable|min:0',
        ]);

        $variant = ProductVariant::create($request->all());

        return response()->json([
            'message' => 'Thêm mới thành công',
            'variant' => $variant
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $variant = ProductVariant::with(['product', 'cover'])->find($id);
        if (!$variant) {
            return response()->json(['message' => 'Không tồn tại'], 404);
        }
        return new VariantResoure($variant);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $variant = ProductVariant::find($id);
        if (!$variant) {
            return response()->json(['message' => 'Không tồn tại'], 404);
        }

        $request->validate([
            'product_id' => 'required',
            'cover_id' => 'required',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|min:0',
            'promotion' => 'nullable|min:0',
        ]);

        $variant->update($request->all());

        return response()->json([
            'message' => 'Sửa thành công',
            'variant' => $variant
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $variant = ProductVariant::find($id);
        if (!$variant) {
            return response()->json(['message' => 'Không tồn tại'], 404);
        }

        // $variant->delete();
        $variant->update(['del_flg' => 1]);

        return response()->json(['message' => 'Ẩn thành công']);
    }
}
