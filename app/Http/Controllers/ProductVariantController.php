<?php

namespace App\Http\Controllers;

use App\Http\Resources\VariantResoure;
use App\Models\Product;
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
    public function show(string $code)
    {
        $variants = ProductVariant::with(['product', 'cover'])
            ->whereHas('product', function ($query) use ($code) {
                $query->where('code', $code);
            })
            ->get();

        if ($variants->isEmpty()) {
            return response()->json(['message' => 'Không tồn tại'], 404);
        }

        return VariantResoure::collection($variants);
    }

    public function getByProductAndCover(string $code, int $cover_id)
    {
        $variant = ProductVariant::with(['product', 'cover'])
            ->whereHas('product', function ($query) use ($code): void {
                $query->where('code', $code);
            })
            ->where('cover_id', $cover_id)
            ->first();

        if (!$variant) {
            return response()->json(['message' => "Không tìm thấy biến thể $cover_id của sản phẩm : $code"], 404);
        }

        return new VariantResoure($variant);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $code)
    {
        $product = Product::where('code', $code)->with('variants')->first();
        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        $variant = $product->variants->first();
        if (!$variant) {
            return response()->json(['message' => 'Không tìm thấy biến thể sản phẩm'], 404);
        }

        $request->validate([
            'cover_id' => 'required',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|min:0',
            'promotion' => 'nullable|min:0',
        ]);

        $variant->update($request->only(['cover_id', 'quantity', 'price', 'promotion']));

        return response()->json([
            'message' => 'Sửa thành công',
            'variant' => $variant
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $code)
    {
        $product = Product::where('code', $code)->with('variants')->first();
        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        $variant = $product->variants->first();
        if (!$variant) {
            return response()->json(['message' => 'Không tìm thấy biến thể sản phẩm'], 404);
        }

        // Update del_flg thành 1 để ẩn
        $variant->update(['del_flg' => 1]);

        return response()->json(['message' => 'Ẩn thành công']);
    }
}
