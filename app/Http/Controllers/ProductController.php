<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Genre;
use App\Models\Product;
use App\Models\ProductGenre;
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
        return ProductResource::collection(Product::with('author', 'publisher', 'language', 'category', 'genres', 'images')->get());
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
    public function update(Request $request, $id)
    {
        // dd($request->file('image'), $request->file('images'));
        $product = Product::find($id);
        // echo '<pre>';
        // print_r($request->all());die();

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
                'author_id' => 'sometimes|exists:authors,id',
                'publisher_id' => 'sometimes|exists:publishers,id',
                'description' => 'sometimes',
                'language_id' => 'sometimes|exists:languages,id',
                'category_id' => 'sometimes|exists:categories,id',
                'genres' => 'sometimes|array',
                'genres.*' => 'exists:genres,id',
                'images'
            ]);
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_link); // Xóa file trong storage
                $image->delete(); // Xóa record trong DB
            }
            // ✅ Xử lý ảnh đại diện (image)
            if ($request->hasFile('image')) {

                // Lưu ảnh mới
                $validatedData['image'] = $request->file('image')->store('uploads', 'public');
            }else{
                $validatedData['image'] = null;
            }

            // ✅ Cập nhật thông tin sản phẩm
            $product->update($validatedData);




            // ✅ Cập nhật genres (nếu có gửi lên)
            if ($request->has('genres')) {
                $product->genres()->sync($request->genres);
            }

            // ✅ Xử lý nhiều ảnh (images)
            if ($request->hasFile('images')) {

                // 🔥 Lưu ảnh mới
                $imagePaths = [];
                foreach ($request->file('images') as $imageFile) {
                    $imagePaths[] = ['image_link' => $imageFile->store('uploads', 'public')];
                }
                $product->images()->createMany($imagePaths);

                // dd($imagePaths);
            }

            DB::commit(); // Lưu thay đổi vào database

            return response()->json([
                'message' => 'Cập nhật sản phẩm thành công',
                'product' => new ProductResource($product->load('author', 'publisher', 'language', 'category', 'genres', 'images')),
                'image_url' => $product->image ? asset('storage/' . $product->image) : null,
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