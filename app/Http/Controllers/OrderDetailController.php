<?php

namespace App\Http\Controllers;

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
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(Request $request, $code_order)
    {
        $userId = $request->user()->id;
        // Lấy chi tiết đơn hàng của user
        $order = Order::with('orderDetails.productVariant')
            ->where('code_order', $code_order)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        return response()->json([
            'message' => 'Chi tiết đơn hàng',
            'order' => $order
        ]);
    }
}
