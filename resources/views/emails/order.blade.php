@php
    $total = $order->orderDetails->sum('total_line');
    $shipping = 30000;
    $voucherDiscount = $order->voucher->discount_amount ?? 0;
    $finalTotal = max(0, $total + $shipping - $voucherDiscount);
@endphp

<div style="font-family: Arial, sans-serif; max-width: 600px; margin: auto; background-color: #ffffff; border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px;">
    <h2 style="color: #1e88e5;">🛍️ Cảm ơn bạn đã đặt hàng!</h2>
    <p style="font-size: 16px; color: #333;">Xin chào <strong>{{ $order->user->name ?? 'Khách hàng' }}</strong>,</p>
    <p style="font-size: 16px; color: #333;">Đơn hàng <strong>#{{ $order->code_order }}</strong> của bạn đã được ghi nhận.</p>

    <hr style="margin: 20px 0;">

    <h3 style="color: #1e88e5;">📋 Thông tin đơn hàng</h3>
    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
        <tr>
            <td><strong>Trạng thái:</strong></td>
            <td>{{ $order->status->name }}</td>
        </tr>
        <tr>
            <td><strong>Mã giảm giá:</strong></td>
            <td>{{ $order->voucher->code ?? 'Không có' }}</td>
        </tr>
        <tr>
            <td><strong>Giá trị giảm:</strong></td>
            <td>{{ number_format($voucherDiscount) }} đ</td>
        </tr>
        <tr>
            <td><strong>Phí vận chuyển:</strong></td>
            <td>{{ number_format($shipping) }} đ</td>
        </tr>
        <tr>
            <td><strong>Tổng thanh toán:</strong></td>
            <td><strong style="color: #d32f2f;">{{ number_format($finalTotal) }} đ</strong></td>
        </tr>
    </table>

    <hr style="margin: 20px 0;">

    <h3 style="color: #1e88e5;">🛒 Chi tiết sản phẩm</h3>
    <table style="width: 100%; border: 1px solid #ccc; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f5f5f5;">
                <th style="border: 1px solid #ccc; padding: 8px;">Sản phẩm</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Biến thể</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Số lượng</th>
                <th style="border: 1px solid #ccc; padding: 8px;">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderDetails as $detail)
                <tr>
                    <td style="border: 1px solid #ccc; padding: 8px;">{{ $detail->productVariant->product->title }}</td>
                    <td style="border: 1px solid #ccc; padding: 8px;">{{ $detail->productVariant->cover->type ?? 'Không rõ' }}</td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: center;">{{ $detail->quantity }}</td>
                    <td style="border: 1px solid #ccc; padding: 8px; text-align: right;">{{ number_format($detail->total_line) }} đ</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr style="margin: 20px 0;">

    <p style="font-size: 14px; color: #555;">📬 Bạn có thể xem lại đơn hàng tại:</p>
    <p>
        <a href="{{ route('orders.show', $order->code_order) }}" style="color: #ffffff; background-color: #1e88e5; padding: 10px 20px; border-radius: 5px; text-decoration: none; display: inline-block;">
            Xem đơn hàng online
        </a>
    </p>

    <p style="font-size: 14px; color: #999;">Nếu có bất kỳ câu hỏi nào, xin hãy liên hệ với chúng tôi qua email hoặc hotline hỗ trợ. ❤️</p>
</div>
