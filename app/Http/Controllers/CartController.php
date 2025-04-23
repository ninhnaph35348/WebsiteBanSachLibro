<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Mail\OrderMail;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Voucher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
                'shipping_name' => 'nullable|string|max:255',
                'shipping_email' => 'nullable|email|max:255',
                'shipping_phone' => 'nullable|string|max:20',
                'shipping_address' => 'nullable|string|max:500',
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
                $price_product = $variant->promotion ?? $variant->price;
                $subtotal = $price_product * $item['quantity'];
                $totalProductPrice += $subtotal;

                // Chuẩn bị dữ liệu cho order_details
                $orderDetails[] = [
                    'product_variant_id' => $variant->id,
                    'quantity' => $item['quantity'],
                    'total_line' => $subtotal,
                ];
            }

            // Phí vận chuyển (nếu không có thì mặc định = 0)
            $shippingFee = $request->shipping_fee ?? 0;

            // Áp dụng voucher (nếu có)
            $discount = 0;
            $voucherId = null;

            // Kiểm tra người dùng có đăng nhập không
            $user = auth('api')->user();

            if ($request->voucher_code && !$user) {
                return response()->json(['message' => 'Bạn cần đăng nhập để sử dụng mã giảm giá!'], 401);
            }

            if ($request->voucher_code) {
                $voucher = Voucher::where('code', $request->voucher_code)
                    ->where('valid_from', '<=', now())
                    ->where('valid_to', '>=', now())
                    ->where('status', 0) // chỉ lấy voucher còn hiệu lực
                    ->first();

                if (!$voucher) {
                    return response()->json(['message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn!'], 400);
                }

                if ($voucher->quantity <= 0) {
                    return response()->json(['message' => 'Mã giảm giá đã hết lượt sử dụng!'], 400);
                }
                $voucherId = $voucher->id;

                if ($user) {
                    // Kiểm tra user đã dùng voucher này chưa
                    $hasUsed = DB::table('voucher_user')
                        ->where('user_id', $user->id)
                        ->where('voucher_id', $voucherId)
                        ->where('status', 'success') // đã dùng thành công
                        ->exists();

                    if ($hasUsed) {
                        return response()->json(['message' => 'Bạn đã sử dụng mã giảm giá này rồi!'], 400);
                    }
                }

                // Tính giảm giá
                if ($voucher->discount_type === 'percent') {
                    $discount = $totalProductPrice * ($voucher->discount / 100);
                    if ($voucher->max_discount) {
                        $discount = min($discount, $voucher->max_discount);
                    }
                } elseif ($voucher->discount_type === 'fixed') {
                    $discount = $voucher->discount;
                } else {
                    return response()->json(['message' => 'Loại giảm giá không hợp lệ!'], 400);
                }

                // Kiểm tra đơn hàng có đạt min_order_value không
                if ($voucher->min_order_value && $totalProductPrice < $voucher->min_order_value) {
                    return response()->json(['message' => 'Đơn hàng chưa đủ điều kiện áp dụng voucher.'], 400);
                }
            }

            // Tính tổng tiền sau khi áp dụng giảm giá
            $totalPrice = max(0, $totalProductPrice + $shippingFee - $discount);


            // Nếu đã đăng nhập, lấy thông tin user từ hệ thống, nếu không thì lấy từ request
            $orderData = [
                'code_order' => $codeOrder,
                'total_price' => $totalPrice,
                'note' => $request->note,
                'order_status_id' => 1,
                'payment_method' => $request->payment_method,
                'voucher_id' => $voucherId,
                'user_id' => $user ? $user->id : null,
                'shipping_name' => $request->shipping_name ?? ($user ? $user->fullname : null), // Lưu tên người nhận
                'shipping_email' => $request->shipping_email ?? ($user ? $user->email : null), // Lưu email người nhận
                'shipping_phone' => $request->shipping_phone ?? ($user ? $user->phone : null), // Lưu số điện thoại người nhận
                'shipping_address' => $request->shipping_address ?? ($user ? $user->address : null), // Lưu địa chỉ người nhận
            ];

            // Nếu đã đăng nhập, bạn có thể lấy thêm thông tin người dùng (người đặt)
            if ($user) {
                $orderData['user_name'] = $user->fullname; // Lưu tên người đặt (người dùng)
                $orderData['user_email'] = $user->email; // Lưu email người đặt
                $orderData['user_phone'] = $user->phone; // Lưu số điện thoại người đặt
                $orderData['user_address'] = $user->address; // Lưu địa chỉ người đặt
            } else {
                // Nếu chưa đăng nhập, lấy từ request
                $orderData['user_name'] = $request->user_name ?? $request->shipping_name;
                $orderData['user_email'] = $request->user_email ?? $request->shipping_email;
                $orderData['user_phone'] = $request->user_phone ?? $request->shipping_phone;
                $orderData['user_address'] = $request->user_address ?? $request->shipping_address;
            }

            // Tạo đơn hàng
            $order = Order::create($orderData);

            if ($voucherId && $user) {

                // Cập nhật số lượng voucher đã sử dụng
                $voucher->decrement('quantity');
                $voucher->increment('used');

                DB::table('voucher_user')->insert([
                    'user_id' => $user->id,
                    'voucher_id' => $voucherId,
                    'used_at' => now(),
                    'status' => 'success',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            foreach ($orderDetails as &$detail) {
                $detail['order_id'] = $order->id;
            }
            DB::table('order_details')->insert($orderDetails);

            DB::commit();
            try {
                Mail::to($order->user_email)->send(new OrderMail($order));
            } catch (\Exception $e) {
                Log::error('Lỗi gửi mail: ' . $e->getMessage());
            }
            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'total_price_cart' => $totalPrice,
                'order' => [
                    'id' => $order->id,
                    'code_order' => $order->code_order,
                    'total_price' => $order->total_price,
                    'note' => $order->note,
                    'user_name' => $order->user_name,
                    'user_email' => $order->user_email,
                    'user_phone' => $order->user_phone,
                    'user_address' => $order->user_address,
                    'payment_method' => $order->payment_method == 0 ? 'COD' : 'VNPay',
                    'shipping_name' => $order->shipping_name,
                    'shipping_email' => $order->shipping_email,
                    'shipping_phone' => $order->shipping_phone,
                    'shipping_address' => $order->shipping_address,
                    'status' => $order->status ? $order->status->name : null,
                    'voucher' => $order->voucher->code ?? null,
                    'user' => $order->user ? $order->user->username : null,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s')
                ],
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

    public function cancelOrder($order_code)
    {
        // Lấy thông tin đơn hàng từ database
        $order = Order::where('code_order', $order_code)->first();

        if (!$order) {
            return response()->json(['message' => 'Đơn hàng không tồn tại!'], 404);
        }

        // Kiểm tra quyền của người dùng
        if (auth('api')->user()->role !== 'sadmin' && auth('api')->user()->role !== 'admin') {
            // Nếu không phải admin hoặc super admin thì chỉ có thể hủy đơn của chính mình
            if (auth('api')->id() !== $order->user_id) {
                return response()->json(['message' => 'Bạn không có quyền hủy đơn hàng này!'], 403);
            }
        }

        // Kiểm tra trạng thái đơn hàng (chỉ hủy được đơn hàng có trạng thái < 3)
        if ($order->order_status_id >= 3) {
            return response()->json(['message' => 'Đơn hàng không thể hủy khi đã ở trạng thái xử lý hoặc đã giao!'], 400);
        }

        // Kiểm tra nếu khách hàng chỉ được hủy khi đơn hàng có status = 1
        if ($order->order_status_id == 1) {
            // Logic hủy đơn hàng
            $order->order_status_id = 7; // 7: trạng thái hủy đơn hàng
            $order->save();

            // Hoàn lại số lượng sản phẩm cho mỗi chi tiết đơn hàng
            $orderDetails = $order->orderDetails; // Lấy thông tin chi tiết đơn hàng liên kết với order
            if ($orderDetails->isEmpty()) {
                return response()->json(['message' => 'Không có chi tiết đơn hàng để hủy!'], 400);
            }

            foreach ($orderDetails as $detail) {
                $productVariant = $detail->productVariant;
                if ($productVariant) {
                    $productVariant->quantity += $detail->quantity; // Cộng lại số lượng sản phẩm
                    $productVariant->save();
                }
            }

            // Hoàn lại voucher nếu có
            if ($order->voucher_id) {
                DB::table('voucher_user')
                    ->where('voucher_id', $order->voucher_id)
                    ->where('user_id', $order->user_id)
                    ->update(['status' => 'failed']); // Đánh dấu voucher đã được hoàn lại
            }

            return response()->json(['message' => 'Đơn hàng đã được hủy thành công!'], 200);
        } elseif ($order->order_status_id == 2) {
            // Nếu trạng thái đơn hàng là 2 (Đang xử lý), yêu cầu admin phê duyệt
            return response()->json(['message' => 'Đơn hàng đang xử lý, yêu cầu admin phê duyệt hủy đơn!'], 400);
        } else {
            // Nếu không phải trạng thái 1 hoặc 2
            return response()->json(['message' => 'Không thể hủy đơn hàng ở trạng thái này!'], 400);
        }
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
