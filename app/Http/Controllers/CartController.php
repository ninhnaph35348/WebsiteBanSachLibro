<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    // Đặt hàng từ giỏ hàng (FE gửi giỏ hàng)
    public function checkout(Request $request)
    {
        try {
            // Validate dữ liệu đầu vào
            $request->validate([
                'cart' => 'required|array|min:1',
                'cart.*.product_variant_id' => 'required|exists:product_variants,id',
                'cart.*.quantity' => 'required|integer|min:1',
                'note' => 'nullable|string',
                'payment_method' => 'required',
                'voucher_id' => 'nullable|integer|exists:vouchers,id',
                'shipping_fee' => 'nullable|numeric|min:0',
                'user_name' => 'nullable|string|max:255',
                'user_email' => 'nullable|email|max:255',
                'user_phone' => 'nullable|string|max:20',
                'user_address' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();
            // Tạo mã đơn hàng
            $codeOrder = 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

            // Tính tổng giá trị đơn hàng
            $totalProductPrice = 0;
            $orderDetails = [];

            foreach ($request->cart as $item) {
                $variant = ProductVariant::findOrFail($item['product_variant_id']);

                if ($variant->quantity < $item['quantity']) {
                    return response()->json(['message' => "Sản phẩm {$variant->product->title} không đủ hàng"], 400);
                }

                // Lấy giá khuyến mãi nếu có
                $price = $variant->promotion ?? $variant->price;
                $subtotal = $price * $item['quantity'];
                $totalProductPrice += $subtotal;

                // Chuẩn bị dữ liệu cho order_details
                $orderDetails[] = [
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                ];

                // Trừ số lượng tồn kho
                $variant->decrement('quantity', $item['quantity']);
            }

            // Phí vận chuyển (nếu không có thì mặc định = 0)
            $shippingFee = $request->shipping_fee ?? 0;

            // Áp dụng voucher (nếu có)
            $discount = 0;
            if ($request->voucher_id) {
                $voucher = Voucher::find($request->voucher_id);
                if ($voucher) {
                    $discount = $voucher->discount_amount;
                }
            }

            // Tính tổng tiền đơn hàng
            $totalPrice = max(0, $totalProductPrice + $shippingFee - $discount);

            // Tạo đơn hàng
            $order = Order::create([
                'code_order' => $codeOrder,
                'total_price' => $totalPrice,
                'note' => $request->note,
                'order_status_id' => 1, // Mặc định = 1 (Chờ xác nhận)
                'payment_method' => $request->payment_method,
                'voucher_id' => $request->voucher_id,
                'user_id' => auth()->id(),
                'user_name' => $request->user_name,
                'user_email' => $request->user_email,
                'user_phone' => $request->user_phone,
                'user_address' => $request->user_address,
            ]);

            // Lưu chi tiết đơn hàng
            foreach ($orderDetails as &$detail) {
                $detail['order_id'] = $order->id;
            }
            DB::table('order_details')->insert($orderDetails);

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order' => $order,
                'order_details' => $orderDetails
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi khi đặt hàng!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
