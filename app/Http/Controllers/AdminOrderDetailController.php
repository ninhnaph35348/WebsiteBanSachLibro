<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminOrderDetailResource;
use App\Http\Resources\AdminOrderResource;
use App\Models\OrderDetail;
use App\Models\Order; // Thêm model Order
use Illuminate\Http\Request;

class AdminOrderDetailController extends Controller
{
    // Hiển thị chi tiết đơn hàng cho admin theo order_code
    public function show($code_order)
    {
        $order = Order::with([
            'orderDetails.productVariant.product',
            'orderDetails.productVariant.cover',
            'status',
            'user',
            'voucher'
        ])->where('code_order', $code_order)->firstOrFail();

        return new AdminOrderResource($order);
    }
}
