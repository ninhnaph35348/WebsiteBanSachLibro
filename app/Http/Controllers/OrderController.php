<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderController extends Controller
{
    // Lấy danh sách đơn hàng
    public function index()
    {
        return response()->json(Order::all());
    }

    // Lấy thông tin 1 đơn hàng
    public function show($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found']);
        }
        return response()->json($order);
    }

    // Tạo mới đơn hàng
    public function store(Request $request)
{
    $request->validate([
        'code_order' => 'required|string|unique:orders',
        'total_price' => 'required|numeric',
        'note' => 'nullable|string',
        'order_status_id' => 'required|integer',
        'payment_id' => 'required|integer',
        'voucher_id' => 'nullable|integer',
        'user_id' => 'required|integer',
    ]);

    $order = Order::create($request->all());

    return response()->json([
        'message' => 'Đơn hàng đã được tạo thành công!',
        'data' => $order
    ], 201);
}


    // Cập nhật đơn hàng
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found']);
        }

        $validated = $request->validate([
            'code_order' => 'sometimes|required|string|max:50',
            'total_price' => 'sometimes|required|numeric',
            'note' => 'sometimes|required|string',
            'order_status_id' => 'sometimes|required|exists:order_statuses,id',
            'payment_id' => 'sometimes|required|exists:payment_methods,id',
            'voucher_id' => 'nullable|exists:vouchers,id',
            'user_id' => 'sometimes|required|exists:users,id',
        ]);

        $order->update($validated);
        return response()->json(['mer' =>'Sửa thành công ',
        'dâ'=> $order]);
    }

    // Xóa đơn hàng
    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $order->delete();
        return response()->json(['message' => 'Order deleted'], Response::HTTP_OK);
    }
}
