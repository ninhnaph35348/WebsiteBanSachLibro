<?php

namespace App\Http\Controllers\Mails;

use App\Http\Controllers\Controller;
use App\Mail\OrderMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendOrderConfirmation($order)
    {
        Mail::to($order->user->email)->send(new OrderMail($order));
    }


    public function show($code_order)
    {
        $order = Order::with(['orderDetails.productVariant.product', 'voucher', 'status', 'user'])
            ->where('code_order', $code_order)
            ->first();

        if (!$order) {
            return view('orders.notfound'); // tạo view nếu không tìm thấy
        }

        return view('emails.orderDetail', compact('order'));
    }
}
