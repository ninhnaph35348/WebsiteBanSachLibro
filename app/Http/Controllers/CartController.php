<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
                'voucher_code' => 'nullable|string|exists:vouchers,code',
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
                    'price' => $subtotal,
                ];

                // Trừ số lượng tồn kho
                $variant->decrement('quantity', $item['quantity']);
            }

            // Phí vận chuyển (nếu không có thì mặc định = 0)
            $shippingFee = $request->shipping_fee ?? 0;

            // Áp dụng voucher (nếu có)
            $discount = 0;
            $voucherId = null;

            if ($request->voucher_code) {
                $voucher = Voucher::where('code', $request->voucher_code)
                    ->where('valid_from', '<=', now()) // Voucher có hiệu lực
                    ->where('valid_to', '>=', now()) // Chưa hết hạn
                    ->first();

                if ($voucher) {
                    $discount = $voucher->discount;
                    $voucherId = $voucher->id; // Lưu voucher_id để lưu vào đơn hàng
                } else {
                    return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn!'], 400);
                }
            }

            // Tính tổng tiền sau khi áp dụng giảm giá
            $totalPrice = max(0, $totalProductPrice + $shippingFee - $discount);

            // Kiểm tra người dùng có đăng nhập không
            $userId = auth()->check() ? auth()->id() : null;

            // Nếu đã đăng nhập, lấy thông tin user từ hệ thống, nếu không thì lấy từ request
            $orderData = [
                'code_order' => $codeOrder,
                'total_price' => $totalPrice,
                'note' => $request->note,
                'order_status_id' => 1,
                'payment_method' => $request->payment_method,
                'voucher_id' => $voucherId,
                'user_id' => $userId,
            ];

            if ($userId) {
                // Nếu user đăng nhập, chỉ lưu nếu FE gửi thông tin
                $orderData['user_name'] = $request->user_name ?? auth()->user()->username;
                $orderData['user_email'] = $request->user_email ?? auth()->user()->email;
                $orderData['user_phone'] = $request->user_phone ?? auth()->user()->phone;
                $orderData['user_address'] = $request->user_address ?? auth()->user()->address;
            } else {
                // Nếu user không đăng nhập, lưu thông tin từ request
                $orderData['user_name'] = $request->user_name;
                $orderData['user_email'] = $request->user_email;
                $orderData['user_phone'] = $request->user_phone;
                $orderData['user_address'] = $request->user_address;
            }

            // Tạo đơn hàng
            $order = Order::create($orderData);

            foreach ($orderDetails as &$detail) {
                $detail['order_id'] = $order->id;
            }
            DB::table('order_details')->insert($orderDetails);

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'total_price_cart' => $totalPrice,
                'order' => new OrderResource($order),
                'order_details' => $orderDetails,
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
