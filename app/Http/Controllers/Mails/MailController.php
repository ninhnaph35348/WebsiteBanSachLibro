<?php

namespace App\Http\Controllers\Mails;

use App\Http\Controllers\Controller;
use App\Mail\OrderMail;
use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendOrderConfirmation($order)
    {
        Mail::to($order->user->email)->send(new OrderMail($order));
    }

    public function cancelOrder(Request $request, $order_code)
    {
        $order = Order::where('code_order', $order_code)->firstOrFail();
        return $this->processCancel($order);
    }

    public function show($code_order)
    {
        $order = Order::with(['orderDetails.productVariant.product', 'voucher', 'status', 'user'])
            ->where('code_order', $code_order)
            ->first();

        return view('emails.orderDetail', compact('order'));
    }
    public function cancelFromEmail($code_order)
    {
        $order = Order::where('code_order', $code_order)->firstOrFail();

        if ($order->order_status_id != 1) { // Nếu không phải "Chờ xử lý"
            return redirect()->route('orders.show', $order->code_order)
                ->with('error', 'Đơn hàng không thể hủy!');
        }

        $order->order_status_id = 7; // 7 = Đã hủy
        $order->save();

        return redirect()->route('orders.show', $order->code_order)
            ->with('success', 'Đơn hàng đã được hủy thành công!');
    }
    private function processCancel($order, $isAdmin = false)
    {
        DB::beginTransaction();
        try {
            // Hoàn số lượng hàng
            foreach ($order->details as $detail) {
                $variant = ProductVariant::find($detail->product_variant_id);
                if ($variant) {
                    $variant->quantity += $detail->quantity;
                    $variant->save();
                }
            }

            // Cộng lại voucher nếu có
            if ($order->voucher_id && $order->user_id) {
                DB::table('voucher_user')
                    ->where('voucher_id', $order->voucher_id)
                    ->where('user_id', $order->user_id)
                    ->update(['status' => 'canceled']);
            }

            // Cập nhật trạng thái đơn hàng
            $order->order_status_id = $isAdmin ? 99 : 4; // 99: admin hủy, 4: hủy thường
            $order->save();

            DB::commit();
            return response()->json(['message' => 'Đơn hàng đã được hủy thành công!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Hủy đơn thất bại!', 'error' => $e->getMessage()], 500);
        }
    }
}
