<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReviewResource;
use Illuminate\Http\Request;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Lấy danh sách tất cả reviews
    public function index()
    {
        $reviews = Review::where('del_flg', 0)->get();
        return response()->json(ReviewResource::collection($reviews), 200);
    }

    // Lấy chi tiết một review
    public function show($id)
{
    $review = Review::with('product')->find($id);

    if (!$review) {
        return response()->json(['message' => 'Không tìm thấy đánh giá'], 404);
    }

    return new ReviewResource($review);
}


    // Tạo mới một review

    public function store(Request $request)
    {
        try {
            $request->validate([
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'required|string|max:255',
                'product_id' => 'required|exists:products,id',
            ]);

            $review = Review::create([
                'rating' => $request->rating,
                'review' => $request->review,
                'status' => 1,
                'product_id' => $request->product_id,
                'user_id' => Auth::id(),

            ]);

            return response()->json(['message' => 'Đánh giá đã được tạo thành công', 'review' => $review], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // Cập nhật review
    // Cập nhật review
    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Không tìm thấy đánh giá'], 404);
        }

        $review->update(['status' => 0]); // Cập nhật status = 0, không cho phép cập nhật giá trị khác

        return response()->json(['message' => 'Cập nhật trạng thái thành công', 'review' => $review], 200);
    }



    // Xóa review



    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Không tìm thấy đánh giá'], 404);
        }

        $review->delete();
        // $review->update(['del_flg' => 1]);

        return response()->json(['message' => 'Đánh giá đã xóa thành công'], 200);
    }


    public function hidden($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Không tìm thấy đánh giá'], 404);
        }

        // $review->delete();
        $review->update(['del_flg' => 1]);

        return response()->json(['message' => 'Ẩn bình luận thành công'], 200);
    }
}
