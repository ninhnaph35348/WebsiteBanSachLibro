<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // Lấy danh sách đơn hàng
    public function index()
    {
        $orders = Order::with(['status', 'voucher', 'user'])
            ->orderBy('created_at', 'desc') // Sắp xếp mới nhất trước
            ->get();

        return OrderResource::collection($orders);
    }



    // Lấy thông tin 1 đơn hàng
    public function show($code_order)
    {
        $order = Order::where('code_order', $code_order)->first();
        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }
        return new OrderResource(
            $order->load(['status', 'voucher', 'user'])
        );
    }


    // Cập nhật đơn hàng


    public function update(Request $request, $code_order)
    {
        $order = Order::where('code_order', $code_order)->first();
        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        // Nếu trạng thái đơn hàng đã là 6, 7, hoặc 8 thì không thể cập nhật nữa
        if (in_array($order->order_status_id, [6, 7, 8])) {
            return response()->json(['message' => 'Không thể thay đổi trạng thái đơn hàng này'], 400);
        }

        if ($request['order_status_id'] <= $order->order_status_id) {
            return response()->json(['message' => 'Không thể thay đổi trạng thái trước đó'], 400);
        }

        if ($order->order_status_id >= 3 && $request['order_status_id'] == 7) {
            return response()->json(['message' => 'Không thể hủy nếu đơn hàng đã đang chuẩn bị hàng trở lên'], 400);
        }

        // Cập nhật trạng thái đơn hàng
        $order->update(['order_status_id' => $request['order_status_id']]);

        // Lấy tên trạng thái đơn hàng
        $orderStatus = DB::table('order_statuses')->where('id', $request['order_status_id'])->value('name');

        return response()->json([
            'message' => 'Cập nhật trạng thái đơn hàng thành công',
            'data' => [
                'code_order' => $order->code_order,
                'order_status_name' => $orderStatus, // Hiển thị tên trạng thái
            ]
        ]);
    }
}
