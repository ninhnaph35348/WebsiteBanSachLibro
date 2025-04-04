<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminOrderDetailResource;
use App\Models\OrderDetail;
use App\Models\Order; // Thêm model Order
use Illuminate\Http\Request;

class AdminOrderDetailController extends Controller
{
    // Hiển thị chi tiết đơn hàng cho admin theo order_code
    public function show($code_order)
    {
        // Tìm đơn hàng theo order_code
        $order = Order::where('code_order', $code_order)->firstOrFail();

        // Lấy chi tiết đơn hàng từ order
        $orderDetails = OrderDetail::where('order_id', $order->id)->get();

        // Trả về dữ liệu dưới dạng resource cho mỗi chi tiết đơn hàng
        return AdminOrderDetailResource::collection($orderDetails);
    }
}
