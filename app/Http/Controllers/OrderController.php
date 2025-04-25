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
        $order = Order::with('orderDetails')->where('code_order', $code_order)->first();
        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $newStatus = $request['order_status_id'];
        $currentStatus = $order->order_status_id;

        if (in_array($currentStatus, [6, 7, 8])) {
            return response()->json(['message' => 'Không thể thay đổi trạng thái đơn hàng này'], 400);
        }

        if ($newStatus < $currentStatus) {
            return response()->json(['message' => 'Không thể thay đổi về trạng thái trước đó'], 400);
        }

        if ($newStatus == 7) {
            if ($currentStatus < 3) {
                // Cho phép hủy đơn
            } else {
                return response()->json(['message' => 'Không thể hủy nếu đơn hàng đã đang chuẩn bị hàng trở lên'], 400);
            }
        } else {
            if ($newStatus != $currentStatus + 1) {
                return response()->json(['message' => 'Vui lòng cập nhật trạng thái theo thứ tự từng bước'], 400);
            }
        }

        DB::beginTransaction();
        try {
            // ✅ Cập nhật trạng thái đơn hàng
            $order->update(['order_status_id' => $newStatus]);

            DB::commit();

            $orderStatus = DB::table('order_statuses')->where('id', $newStatus)->value('name');

            return response()->json([
                'message' => 'Cập nhật trạng thái đơn hàng thành công',
                'data' => [
                    'code_order' => $order->code_order,
                    'order_status_name' => $orderStatus,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Đã xảy ra lỗi khi cập nhật trạng thái đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
