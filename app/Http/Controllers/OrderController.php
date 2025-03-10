<?php

namespace App\Http\Controllers;

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
        return response()->json(Order::all());
    }

    // Lấy thông tin 1 đơn hàng
    public function show($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'], 404);
        }
        return response()->json($order);
    }



    public function store(Request $request)
    {
        // dd($request->all());
        try {
            $request->validate([
                'products' => 'required|array', // Danh sách sản phẩm [{variant_id, quantity}]
                'products.*.id' => 'required|integer|exists:product_variants,id',
                'products.*.quantity' => 'required|integer|min:1',
                'note' => 'nullable|string',
                'payment_method' => 'required|string',
                'voucher_id' => 'nullable|integer|exists:vouchers,id',
                'user_id' => 'required|integer|exists:users,id',
                'shipping_fee' => 'nullable|numeric|min:0', // Phí vận chuyển
            ]);
            DB::beginTransaction();

            // Tạo mã đơn hàng
            $codeOrder = 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));

            // Tính tổng giá trị sản phẩm từ `product_variants`
            $totalProductPrice = 0;
            $orderDetails = [];

            foreach ($request->products as $product) {
                $variant = ProductVariant::findOrFail($product['id']);
                $productInfo = Product::findOrFail($variant->product_id); // Lấy thông tin từ bảng `products`

                // Nếu có giá khuyến mãi thì lấy, không thì lấy giá gốc
                $price = $variant->promotion ?? $variant->price;
                $totalProductPrice += $price * $product['quantity'];

                // Chuẩn bị dữ liệu cho order_details (chi tiết đơn hàng)
                $orderDetails[] = [
                    'product_variant_id' => $variant->id,
                    'quantity' => $product['quantity'],
                    'price' => $price,
                ];
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
                'order_status_id' => $request->order_status_id ?? 1, // Mặc định = 1 (Chờ xác nhận)
                'payment_method' => $request->payment_method,
                'voucher_id' => $request->voucher_id,
                'user_id' => $request->user_id,
            ]);

            // Lưu chi tiết đơn hàng vào `order_details`
            foreach ($orderDetails as &$detail) {
                $detail['order_id'] = $order->id;
            }
            DB::table('order_details')->insert($orderDetails);

            DB::commit();

            return response()->json([
                'message' => 'Đơn hàng đã được tạo thành công!',
                'data' => [
                    'order' => $order,
                    'order_details' => $orderDetails
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi khi tạo đơn hàng!',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Cập nhật đơn hàng
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
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

        if($order->order_status_id >=3 && $request['order_status_id'] == 7){
            return response()->json(['message' => 'Không thể hủy nếu đơn hàng đã đang chuẩn bị hàng trở lên'], 400);
        }

        $order->update(['order_status_id' => $request['order_status_id']]);

        return response()->json([
            'message' => 'Cập nhật trạng thái đơn hàng thành công',
            'data' => $order
        ]);
    }


    // Xóa đơn hàng
    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['message' => 'Không tìm thấy đơn hàng'],);
        }

        $order->delete();
        return response()->json(['message' => 'Order deleted'],);
    }
}
