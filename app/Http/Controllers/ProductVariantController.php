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
            ->orderBy('id', 'desc')
            ->get();
        return VariantResoure::collection($products);
    }
    public function getAllProductVariantByStatus()
    {
        $products = ProductVariant::with(['product', 'cover'])
            ->where('del_flg', 0)
            ->whereHas('product', function ($query) {
                $query->where('status', 'in_stock');
            })
            ->orderBy('id', 'desc')
            ->get();

        return VariantResoure::collection($products);
    }

    public function getTop5ProductVarriantByRating()
    {
        $products = ProductVariant::with([
            'product',
            'cover',
        ])
            ->where('del_flg', 0)
            ->whereHas('product', function ($query) {
                $query->where('status', 'in_stock');
            })
            ->get()
            ->sortByDesc(function ($variant) {
                return $variant->product->reviews->avg('rating') ?? 0;
            })
            ->take(5)
            ->values(); // reset index

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
    public function update(Request $request, string $code, int $cover_id)
    {
        // Lấy biến thể theo code sản phẩm và cover_id
        $variant = ProductVariant::with('product')
            ->whereHas('product', function ($query) use ($code): void {
                $query->where('code', $code);
            })
            ->where('cover_id', $cover_id)
            ->first();

        if (!$variant) {
            return response()->json([
                'message' => "Không tìm thấy biến thể $cover_id của sản phẩm: $code"
            ], 404);
        }

        $request->validate([
            'cover_id' => 'required|integer',
            'quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'promotion' => 'nullable|numeric|min:0',
        ]);

        $variant->update($request->only(['cover_id', 'quantity', 'price', 'promotion']));

        return response()->json([
            'message' => 'Cập nhật biến thể thành công!',
            'variant' => $variant
        ]);
    }
    public function updateProductVariantStatus(Request $request, $code)
    {
        $product = Product::where('code', $code)->with('variants')->first();
        if (!$product) {
            return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
        }

        $variant = $product->variants->first();
        if (!$variant) {
            return response()->json(['message' => 'Không tìm thấy biến thể sản phẩm'], 404);
        }

        $validated = $request->validate([
            'del_flg' => 'required|in:0,1',
        ]);

        // Nếu product đã ẩn (status = out_stock) và request muốn hiển thị lại biến thể (del_flg = 0)
        if ($product->status === 'out_stock' && $validated['del_flg'] == 0) {
            return response()->json([
                'message' => 'Không thể hiển thị biến thể vì sản phẩm đang bị ẩn'
            ], 403);
        }

        $variant->update(['del_flg' => $validated['del_flg']]);

        return response()->json([
            'message' => $validated['del_flg'] == 1
                ? 'Biến thể sản phẩm đã bị ẩn'
                : 'Biến thể sản phẩm đã được hiển thị',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $code)
    // {
    //     $product = Product::where('code', $code)->with('variants')->first();
    //     if (!$product) {
    //         return response()->json(['message' => 'Không tìm thấy sản phẩm'], 404);
    //     }

    //     $variant = $product->variants->first();
    //     if (!$variant) {
    //         return response()->json(['message' => 'Không tìm thấy biến thể sản phẩm'], 404);
    //     }

    //     // Update del_flg thành 1 để ẩn
    //     $variant->update(['del_flg' => 1]);

    //     return response()->json(['message' => 'Ẩn thành công']);
    // }
}
