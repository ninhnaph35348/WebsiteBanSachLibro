<div style="font-family: Arial, sans-serif; width: 100%; margin: 0 auto; background: #f9f9f9; border-radius: 8px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">

    <!-- Header -->
    <div style="text-align: center; padding-bottom: 20px;">
        <img src="{{ asset('images/logo.png') }}" alt="Logo" style="width: 120px; margin-bottom: 10px;">
        <h1 style="color: #1e88e5; font-size: 24px; margin: 0;">Cảm ơn bạn đã mua sắm tại chúng tôi!</h1>
    </div>

    <!-- Order and Customer Details -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <h2 style="color: #1e88e5; margin-bottom: 20px;">📦 Chi tiết đơn hàng</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="padding: 10px; border: 1px solid #ddd; width: 50%; vertical-align: top;">
                    <p><strong>📦 Mã đơn hàng:</strong> {{ $order->code_order }}</p>
                    <p><strong>👤 Người nhận:</strong> {{ $order->shipping_name }}</p>
                    <p><strong>📧 Email:</strong> {{ $order->shipping_email }}</p>
                    <p><strong>📞 Điện thoại:</strong> {{ $order->shipping_phone }}</p>
                    <p><strong>📍 Địa chỉ:</strong> {{ $order->shipping_address }}</p>
                    <p><strong>💳 Phương thức thanh toán:</strong> {{ $order->payment_method == 0 ? 'COD' : 'VNPAY' }}</p>
                    <p><strong>🚚 Trạng thái đơn hàng:</strong> {{ $order->status->name ?? 'N/A' }}</p>
                </td>
                <td style="padding: 10px; border: 1px solid #ddd; width: 50%; vertical-align: top;">
                    <h3 style="color: #1e88e5; margin-bottom: 10px;">👤 Thông tin người đặt</h3>
                    <p><strong>📝 Tên người đặt:</strong> {{ $order->user_name }}</p>
                    <p><strong>📧 Email:</strong> {{ $order->user_email }}</p>
                    <p><strong>📞 Điện thoại:</strong> {{ $order->user_phone }}</p>
                    <p><strong>📍 Địa chỉ:</strong> {{ $order->user_address }}</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Products Table -->
    <div style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <h3 style="color: #1e88e5; margin-bottom: 10px;">🛒 Danh sách sản phẩm</h3>
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <thead>
                <tr style="background-color: #f5f5f5;">
                    <th style="padding: 10px; border: 1px solid #ddd;">Hình ảnh</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Sản phẩm</th>
                    <th style="padding: 10px; border: 1px solid #ddd;">Loại bìa</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Số lượng</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Tổng</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderDetails as $detail)
                    <tr>
                        <td style="padding: 10px; border: 1px solid #eee; text-align: center;">
                            @if ($detail->productVariant?->product?->image)
                                <img src="{{ asset('storage/'.$detail->productVariant->product->image) }}" alt="Hình sản phẩm" style="width: 80px; height: auto; border-radius: 4px;">
                            @else
                                <span>Không có hình</span>
                            @endif
                        </td>
                        <td style="padding: 10px; border: 1px solid #eee;">
                            {{ $detail->productVariant?->product?->title ?? 'Sản phẩm không tồn tại' }}
                        </td>
                        <td style="padding: 10px; border: 1px solid #eee;">
                            {{ $detail->productVariant?->cover?->type ?? 'Không rõ' }}
                        </td>
                        <td style="padding: 10px; text-align: center; border: 1px solid #eee;">
                            {{ $detail->quantity }}
                        </td>
                        <td style="padding: 10px; text-align: right; border: 1px solid #eee;">
                            {{ number_format($detail->total_line) }} đ
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
        <div style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
            <p><strong>🎁 Voucher áp dụng:</strong> {{ $order->voucher->code ?? 'Không có'}}</p>
        </div>

        @if ($order->voucher)


            <div style="background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="margin-bottom: 15px;">
                    <p style="font-size: 18px; color: #1e88e5; margin: 0;">
                        <strong>💸 Giảm giá:</strong>
                        @if ($order->voucher->discount_type == 'percent')
                            {{ number_format($order->voucher->discount) }}%
                        @else
                            {{ number_format($order->voucher->discount) }} đ
                        @endif
                    </p>
                </div>
        @endif
            <div style="margin-bottom: 15px;">
                <p style="font-size: 18px; color: #1e88e5; margin: 0;">
                    <strong>🚚 Phí vận chuyển:</strong> {{ number_format(30000) }} đ
                </p>
            </div>

            <div>
                <p style="font-size: 18px; color: #d32f2f; margin: 0;">
                    <strong>💰 Tổng cộng:</strong> {{ number_format($order->total_price) }} đ
                </p>
            </div>
        </div>

    @if (session('success'))
    <div style="background-color: #c8e6c9; color: #256029; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 16px; font-weight: bold;">
        ✅ {{ session('success') }}
    </div>
    @endif

    @if (session('error'))
    <div style="background-color: #ffcdd2; color: #c62828; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 16px; font-weight: bold;">
        ❌ {{ session('error') }}
    </div>
    @endif

    <!-- Footer -->
    <div style="text-align: center; margin-top: 30px;">
        <!-- Nút Quay lại trang chủ -->
        <a href="{{ url('http://localhost:5173/') }}" style="text-decoration: none; background-color: #1e88e5; color: #fff; padding: 10px 20px; border-radius: 5px; display: inline-block; font-size: 16px; margin-right: 10px;">
            🔙 Quay lại trang chủ
        </a>

        <!-- Kiểm tra trạng thái đơn hàng để vô hiệu hóa nút hủy -->
        @if ($order->order_status_id >= 2)
            <!-- Đơn hàng đã có trạng thái 2 hoặc lớn hơn, không cho phép hủy -->
            <button disabled style="background-color: #ddd; color: #fff; padding: 10px 20px; border-radius: 5px; font-size: 16px; margin-top: 10px;">
                ❌ Hủy đơn hàng
            </button>
        @else
            <!-- Nút hủy đơn hàng khi trạng thái là "Chờ xử lý" hoặc thấp hơn -->
            <a href="{{ url('/orders/cancel/'.$order->code_order) }}"
                onclick="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này không?');"
                style="text-decoration: none; background-color: #d32f2f; color: #fff; padding: 10px 20px; border-radius: 5px; font-size: 16px; margin-top: 10px;">
                ❌ Hủy đơn hàng
            </a>
        @endif
    </div>
    </div>

    <hr style="margin: 30px 0; border: 1px solid #ddd;">

    <!-- Footer Section -->
    <div style="text-align: center; font-size: 14px; color: #888;">
        <p>© 2025, Tất cả các quyền được bảo vệ.</p>
        <p>Liên hệ: <a href="mailto:anninh07122004@gmail.com" style="color: #1e88e5; text-decoration: none;">anninh07122004@gmail.com</a></p>
        <p>Hotline: <strong>0345.651.932</strong></p>
    </div>
</div>
