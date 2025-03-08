<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    // Lấy danh sách tất cả reviews
    public function index()
    {
        $reviews = Review::all();
        return response()->json($reviews, 200);
    }

    // Lấy chi tiết một review
    public function show($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Không tìm thấy đánh giá'], 404);
        }

        return response()->json($review, 200);
    }

    // Tạo mới một review
    public function store(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'required|string|max:255',
            'status' => 'required|integer|in:0,1',
        ]);

        $review = Review::create($request->all());

        return response()->json(['message' => 'Đánh giá đã được tạo thành công', 'review' => $review], 201);
    }

    // Cập nhật review
    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Không tìm thấy đánh giá'], 404);
        }

        $request->validate([
            'rating' => 'integer|min:1|max:5',
            'review' => 'string|max:255',
            'status' => 'integer|in:0,1',
        ]);

        $review->update($request->all());

        return response()->json(['message' => 'Đánh giá đã được tạo thành công', 'review' => $review], 200);
    }

    // Xóa review
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Không tìm thấy đánh giá'], 404);
        }

        $review->delete();

        return response()->json(['message' => 'Đánh giá đã xóa thành công'], 200);
    }
}
