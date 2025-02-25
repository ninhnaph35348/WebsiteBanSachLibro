<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Genre;
use App\Models\Product;
use App\Models\ProductGenre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return ProductResource::collection(Product::with('author', 'publisher', 'language', 'category', 'genres', 'images')->get());
    }

    public function store(Request $request)
    {
        try {
            // Validate dữ liệu
            $validatedData = $request->validate([
                'code' => 'required|string|max:50',
                'title' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'promotion' => 'nullable|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'quantity' => 'required|integer|min:0',
                'supplier_name' => 'required|string|max:150',
                'author_id' => 'required|exists:authors,id',
                'publisher_id' => 'required|exists:publishers,id',
                'description' => 'required|string',
                'language_id' => 'required|exists:languages,id',
                'category_id' => 'required|exists:categories,id',
            ]);

            // Xử lý ảnh đại diện (nếu có)
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('uploads', 'public');
                $validatedData['image'] = $imagePath;
            }

            // Tạo sản phẩm
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
    public function show($id)
    {
        $product = Product::find($id);

        // dd($product);

        if (!$product) {
            return response()->json(['message' => "Không tìm thấy sản phẩm"], 404);
        }

        return new ProductResource(Product::with('author', 'publisher', 'language', 'category', 'genres', 'images')->findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            // Validate dữ liệu
            $validatedData = $request->validate([
                'code' => 'required|string|max:50',
                'title' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'promotion' => 'nullable|numeric|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'quantity' => 'required|integer|min:0',
                'supplier_name' => 'required|string|max:150',
                'author_id' => 'required|exists:authors,id',
                'publisher_id' => 'required|exists:publishers,id',
                'description' => 'required|string',
                'language_id' => 'required|exists:languages,id',
                'category_id' => 'required|exists:categories,id',
            ]);

            // Xử lý ảnh đại diện mới nếu có
            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu có
                if ($product->image) {
                    Storage::disk('public')->delete($product->image);
                }
                // Lưu ảnh mới
                $imagePath = $request->file('image')->store('uploads', 'public');
                $validatedData['image'] = $imagePath;
            }

            // Cập nhật thông tin sản phẩm
            $product->update($validatedData);

            // Cập nhật genres nếu có
            if ($request->has('genres')) {
                $product->genres()->sync($request->genres);
            }
//x
            // ✅ Cập nhật ảnh sản phẩm (nếu có)
            if ($request->hasFile('images')) {
                // Xóa ảnh cũ
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image->image_link);
                    $image->delete();
                }

                // Lưu ảnh mới
                foreach ($request->file('images') as $imageFile) {
                    $imagePath = $imageFile->store('uploads', 'public');
                    $product->images()->create(['image_link' => $imagePath]);
                }
            }

            return response()->json([
                'message' => 'Cập nhật sản phẩm thành công',
                'product' => new ProductResource($product->load('author', 'publisher', 'language', 'category', 'genres', 'images')),
                'image_url' => $product->image ? asset('storage/' . $product->image) : null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi cập nhật sản phẩm',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => "Không tìm thấy sản phẩm"], 404);
        }

        try {
            // Xóa ảnh đại diện nếu có
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Xóa ảnh trong bảng multiple_images
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_link); // Xóa file trong storage
                $image->delete(); // Xóa record trong DB
            }

            // Xóa quan hệ với genres (tránh rác trong bảng pivot)
            $product->genres()->detach();

            // Xóa sản phẩm
            $product->delete();

            return response()->json(['message' => 'Xóa sản phẩm thành công'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi xóa sản phẩm',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
