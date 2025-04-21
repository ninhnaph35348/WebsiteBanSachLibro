<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Resources\VariantResoure;
use App\Models\Genre;
use App\Models\Product;
use App\Models\ProductGenre;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('reviews', 'author', 'publisher', 'language', 'category', 'genres', 'images')
            ->where('del_flg', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return ProductResource::collection($products);
    }

    public function getAllProductByStatus()
    {
        $products = Product::with('author', 'publisher', 'language', 'category', 'genres', 'images')
            ->where('del_flg', 0)
            ->where('status', 'in_stock')
            ->orderBy('id', 'desc')
            ->get();

        return ProductResource::collection($products);
    }

    public function product_filtering(Request $request)
    {
        $query = Product::with('author', 'publisher', 'language', 'category', 'genres', 'images', 'variants.cover')
            ->where('del_flg', 0);

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('language_id')) {
            $query->where('language_id', $request->language_id);
        }

        if ($request->has('publisher_id')) {
            $query->where('publisher_id', $request->publisher_id);
        }

        if ($request->has('author_id')) {
            $query->where('author_id', $request->author_id);
        }

        // Lọc theo nhiều thể loại (genre_id)
        if ($request->has('genre_id')) {
            $genreIds = is_array($request->genre_id) ? $request->genre_id : explode(',', $request->genre_id);
            $query->whereHas('genres', function ($q) use ($genreIds) {
                $q->whereIn('genres.id', $genreIds);
            });
        }

        if ($request->has('cover_id')) {
            $query->whereHas('variants', function ($q) use ($request) {
                $q->where('cover_id', $request->cover_id);
            });
        }

        return ProductResource::collection($query->get());
    }



    public function store(Request $request)
    {
        try {
            // Validate dữ liệu
            $validatedData = $request->validate([
                'code' => 'required|string|max:50',
                'title' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'supplier_name' => 'required|string|max:150',
                'published_year' => 'required|integer',
                'book_count' => 'required|integer',
                'author_id' => 'required|exists:authors,id',
                'publisher_id' => 'required|exists:publishers,id',
                'description' => 'required|string',
                'language_id' => 'required|exists:languages,id',
                'category_id' => 'required|exists:categories,id',
            ]);

            if (Product::where('code', $request->code)->exists()) {
                return response()->json(['message' => 'Mã sản phẩm đã tồn tại'], 400);
            }
            // Xử lý ảnh đại diện (nếu có)
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('uploads', 'public');
                $validatedData['image'] = $imagePath;
            }


            // Tạo sản phẩm
            $validatedData['status'] = 'in_stock';
            $product = Product::create($validatedData);

            // Gán genres nếu có
            if ($request->has('genres')) {
                $product->genres()->sync($request->genres);
            }

            // ✅ Lưu nhiều ảnh nếu có
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $imageFile) {
                    $imagePath = $imageFile->store('uploads', 'public');
                    $product->images()->create(['image_link' => $imagePath]);
                }
            }

            return response()->json([
                'message' => 'Thêm mới sản phẩm thành công',
                'product' => new ProductResource($product->load('author', 'publisher', 'language', 'category', 'genres', 'images')),
                'image_url' => $product->image ? asset('storage/' . $product->image) : null,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi tạo sản phẩm',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['message' => "Không tìm thấy sản phẩm"], 404);
        }

        return new ProductResource(
            $product->load('author', 'publisher', 'language', 'category', 'genres', 'images')
        );
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['message' => "Không tìm thấy sản phẩm"], 404);
        }
        try {
            DB::beginTransaction(); // Bắt đầu transaction để tránh lỗi dữ liệu không nhất quán

            // ✅ Validate dữ liệu gửi lên
            $validatedData = $request->validate([
                'title' => 'sometimes|string|max:255',
                'image' => 'nullable',
                'supplier_name' => 'sometimes|string|max:150',
                'published_year' => 'sometimes|integer',
                'book_count' => 'sometimes|integer',
                'author_id' => 'sometimes|exists:authors,id',
                'publisher_id' => 'sometimes|exists:publishers,id',
                'description' => 'sometimes',
                'language_id' => 'sometimes|exists:languages,id',
                'category_id' => 'sometimes|exists:categories,id',
                'genres' => 'sometimes|array',
                'genres.*' => 'exists:genres,id',
                'images'
            ]);

            // ✅ Xử lý ảnh đại diện (image)
            if ($request->hasFile('image')) {
                // Lưu ảnh mới
                $validatedData['image'] = $request->file('image')->store('uploads', 'public');

                // Xóa ảnh cũ nếu có
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
            }

            // ✅ Cập nhật thông tin sản phẩm
            $product->update($validatedData);

            // ✅ Cập nhật genres (nếu có gửi lên)
            if ($request->has('genres')) {
                $product->genres()->sync($request->genres);
            }

            // ✅ Xử lý nhiều ảnh (images)
            if ($request->hasFile('images')) {
                // Xóa ảnh cũ nếu có ảnh mới được tải lên
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image->image_link); // Xóa file trong storage
                    $image->delete(); // Xóa record trong DB
                }

                // 🔥 Lưu ảnh mới
                $imagePaths = [];
                foreach ($request->file('images') as $imageFile) {
                    $imagePaths[] = ['image_link' => $imageFile->store('uploads', 'public')];
                }
                $product->images()->createMany($imagePaths);
            }


            DB::commit(); // Lưu thay đổi vào database

            return response()->json([
                'message' => 'Cập nhật sản phẩm thành công',
                'product' => new ProductResource($product->load('author', 'publisher', 'language', 'category', 'genres', 'images')),
                // 'image_url' => $product->image ? asset('storage/' . $product->image) : null,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); // Hoàn tác nếu có lỗi xảy ra
            return response()->json([
                'message' => 'Lỗi khi cập nhật sản phẩm',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['message' => "Không tìm thấy sản phẩm"], 404);
        }

        try {
            // // Xóa ảnh đại diện nếu có
            // if ($product->image) {
            //     Storage::disk('public')->delete($product->image);
            // }

            // // Xóa ảnh trong bảng multiple_images
            // foreach ($product->images as $image) {
            //     Storage::disk('public')->delete($image->image_link); // Xóa file trong storage
            //     $image->delete(); // Xóa record trong DB
            // }

            // // Xóa quan hệ với genres (tránh rác trong bảng pivot)
            // $product->genres()->detach();


            $product->update(['del_flg' => 1]);

            return response()->json(['message' => 'Sản phẩm đã bị ẩn'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi xóa sản phẩm',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function updateProductStatus(Request $request, $code)
    {
        $product = Product::where('code', $code)->first();

        if (!$product) {
            return response()->json(['message' => "Không tìm thấy sản phẩm"], 404);
        }

        // Validate status truyền vào
        $validated = $request->validate([
            'status' => 'required|in:in_stock,out_stock',
        ]);

        // Cập nhật status sản phẩm
        $product->update(['status' => $validated['status']]);

        // Nếu sản phẩm bị ẩn -> cập nhật tất cả biến thể về del_flg = 1 (ẩn)
        if ($validated['status'] === 'out_stock') {
            $product->variants()->update(['del_flg' => 1]);
        } else {
            // Nếu sản phẩm được hiển thị lại -> cập nhật tất cả biến thể về del_flg = 0 (hiện)
            $product->variants()->update(['del_flg' => 0]); // Biến thể hiển thị
        }

        return response()->json([
            'message' => $validated['status'] === 'out_stock'
                ? 'Sản phẩm và các biến thể đã bị ẩn'
                : 'Sản phẩm đã được hiển thị',
        ], 200);
    }
    public function latest()
    {
        $products = ProductVariant::with(['product', 'cover'])
            ->where('del_flg', 0)
            ->whereHas('product', function ($query) {
                $query->where('status', 'in_stock');
            })
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return VariantResoure::collection($products);
    }
    public function search(Request $request)
    {
        $keyword = $request->input('s');

        $query = ProductVariant::query()
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->select('product_variants.*', 'products.title', 'products.supplier_name', 'products.description', 'products.category_id', 'products.status');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('products.title', 'like', '%' . $keyword . '%')
                    ->orWhere('products.supplier_name', 'like', '%' . $keyword . '%')
                    ->orWhere('products.description', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->has('category_id')) {
            $query->where('products.category_id', $request->input('category_id'));
        }

        if ($request->has('status')) {
            $query->where('products.status', $request->input('status'));
        }

        $products = $query->get();

        return response()->json($products);
    }

    public function bestSellers()
    {
        // Lấy danh sách sản phẩm bán chạy nhất
        $bestSellingVariants = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.order_status_id', 6) // Chỉ lấy đơn hàng đã hoàn tất
            ->select('order_details.product_variant_id', DB::raw('SUM(order_details.quantity) as sold_quantity'))
            ->groupBy('order_details.product_variant_id')
            ->orderByDesc('sold_quantity')
            ->take(10)
            ->get();

        $variants = ProductVariant::with(['product', 'cover'])
            ->where('del_flg', 0)
            ->whereHas('product', function ($query) {
                $query->where('status', 'in_stock');
            })
            ->orderBy('created_at', 'desc')
            ->whereIn('id', $bestSellingVariants->pluck('product_variant_id'))
            ->get();

        $result = $variants->map(function ($variant) use ($bestSellingVariants) {
            $soldQuantity = $bestSellingVariants->firstWhere('product_variant_id', $variant->id)->sold_quantity ?? 0;

            return [
                'id' => $variant->id,
                'quantity' => $variant->quantity,
                'price' => $variant->price,
                'promotion' => $variant->promotion,
                'cover_id' => $variant->cover_id,
                'cover' => $variant->cover ? $variant->cover->type : null,
                'sold_quantity' => $soldQuantity,
                'product' => $variant->product ? [
                    'code' => $variant->product->code,
                    'title' => $variant->product->title,
                    'rating' => round($variant->product->reviews->avg('rating'), 1),
                    'author' => $variant->product->author ? $variant->product->author->name : null,
                    'publisher' => $variant->product->publisher ? $variant->product->publisher->name : null,
                    'language' => $variant->product->language ? $variant->product->language->name : null,
                    'category' => $variant->product->category ? $variant->product->category->name : null,
                    'genres' => $variant->product->genres ? $variant->product->genres->pluck('name') : null,
                    'image' => $variant->product->image,
                    'images' => $variant->product->images ? $variant->product->images->pluck('image_link') : null,
                ] : null,
            ];
        });

        return response()->json(['data' => $result]);
    }
}
