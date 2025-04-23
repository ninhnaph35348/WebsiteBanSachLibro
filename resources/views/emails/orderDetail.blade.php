<div style="font-family: Arial, sans-serif; width: 100%; margin: 0 auto; background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 30px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">

    <!-- Header -->
    <div style="text-align: center; padding-bottom: 20px;">
        <img src="{{ asset('storage/images/logo.png') }}" alt="Logo" style="width: 120px;">
        <h1 style="color: #1e88e5;">Cảm ơn bạn đã mua sắm tại chúng tôi!</h1>
    </div>

    <!-- Order details -->
    <h2 style="color: #1e88e5; margin-bottom: 20px;">📦 Chi tiết đơn hàng: <span style="color: #333;">{{ $order->code_order }}</span></h2>

    <div style="margin-bottom: 20px;">
        <p><strong>👤 Người nhận:</strong> {{ $order->shipping_name }}</p>
        <p><strong>📧 Email:</strong> {{ $order->shipping_email }}</p>
        <p><strong>📞 Điện thoại:</strong> {{ $order->shipping_phone }}</p>
        <p><strong>📍 Địa chỉ:</strong> {{ $order->shipping_address }}</p>
        <p><strong>💳 Phương thức thanh toán:</strong> {{ $order->payment_method == 0 ? 'COD' : 'VNPAY' }}</p>
        <p><strong>🚚 Trạng thái đơn hàng:</strong> {{ $order->status->name ?? 'N/A' }}</p>
    </div>

    <!-- Products Table -->
    <h3 style="color: #1e88e5;">🛒 Danh sách sản phẩm</h3>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th style="padding: 10px; border: 1px solid #ccc;">Hình ảnh</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Sản phẩm</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Số lượng</th>
                <th style="padding: 10px; border: 1px solid #ccc;">Tổng</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderDetails as $detail)
                <tr>
                    <td style="padding: 10px; border: 1px solid #eee; text-align: center;">
                        <img src="{{ asset('storage/'.$detail->productVariant->product->image) }}" alt="Hình sản phẩm" style="width: 80px; height: auto; border-radius: 4px;">
                    </td>
                    <td style="padding: 10px; border: 1px solid #eee;">{{ $detail->productVariant->product->title }}</td>
                    <td style="padding: 10px; text-align: center; border: 1px solid #eee;">{{ $detail->quantity }}</td>
                    <td style="padding: 10px; text-align: right; border: 1px solid #eee;">{{ number_format($detail->total_line) }} đ</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($order->voucher)
        <p><strong>🎁 Voucher áp dụng:</strong> {{ $order->voucher->code }}</p>
    @endif

    <p style="font-size: 18px; color: #d32f2f;"><strong>💰 Tổng cộng:</strong> {{ number_format($order->total_price) }} đ</p>

    <!-- Footer -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ url('http://localhost:5173/') }}" style="text-decoration: none; background-color: #e0e0e0; color: #333; padding: 10px 20px; border-radius: 5px; display: inline-block;">
            🔙 Quay lại trang chủ
        </a>
    </div>

    <hr style="margin: 30px 0; border: 1px solid #ddd;">

    <!-- Footer Section -->
    <div style="text-align: center; font-size: 14px; color: #888;">
        <p>© 2025, Tất cả các quyền được bảo vệ.</p>
        <p>Liên hệ: <a href="mailto:support@yourstore.com" style="color: #1e88e5; text-decoration: none;">support@yourstore.com</a></p>
        <p>Hotline: <strong>1800 1234</strong></p>
    </div>
</div>
