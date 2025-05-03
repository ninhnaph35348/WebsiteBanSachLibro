<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderDetailResource;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderDetailController extends Controller
{
    public function index(Request $request)
    {
        // Lấy user_id từ request
        $userId = $request->user()->id;

        // Lấy danh sách đơn hàng của user
        $orders = Order::with('orderDetails.productVariant')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return OrderResource::collection($orders);
    }

    public function show(Request $request, $code_order)
    {
        // Lấy user_id từ request
        $userId = $request->user()->id ?? null;

        // Tìm đơn hàng theo mã đơn hàng và user_id
        $order = Order::with('orderDetails.productVariant.product')
            ->where('code_order', $code_order)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        // Tính tổng tiền sản phẩm (chưa gồm mã giảm giá, phí vận chuyển)
        $totalProductPrice = $order->orderDetails->sum(function ($detail) {
            $variant = $detail->productVariant;
            $price = ($variant->promotion > 0) ? $variant->promotion : $variant->price;
            return $price * $detail->quantity;
        });

        return response()->json([
            'order' => new OrderResource($order),
            'total_product_price' => $totalProductPrice,
        ]);
    }
}
