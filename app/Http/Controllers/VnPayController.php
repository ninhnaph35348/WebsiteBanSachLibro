<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VnPayController extends Controller
{
    private $vnp_TmnCode = "JJINZW3F"; // Mã website
    private $vnp_HashSecret = "WUB93OSEY5GUUVEHYTPSWEIM0TWNMJRM"; // Chuỗi bí mật
    private $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html"; // Địa chỉ thanh toán
    private $vnp_ReturnUrl = "http://localhost:8000/api/vnpay-return";

    public function createPayment(Request $request)
    {
        $vnp_TxnRef = rand(1000000000, 9999999999);
        $vnp_OrderInfo = $request->orderInfo ?? "Thanh toan don hang test";
        $vnp_Amount = $request->amount * 100;
        $vnp_IpAddr = $request->ip();
        $vnp_CreateDate = date('YmdHis');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $this->vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef
        ];

        ksort($inputData);
        $hashData = urldecode(http_build_query($inputData));
        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);

        $inputData["vnp_SecureHash"] = $secureHash;
        $paymentUrl = $this->vnp_Url . "?" . http_build_query($inputData);

        return response()->json([
            "payment_url" => $paymentUrl,
            "vnp_SecureHash" => $secureHash
        ]);
    }

    public function vnpayReturn(Request $request)
    {
        $input = $request->all();

        // Lấy secure hash từ VNPay
        $vnp_SecureHash = $input['vnp_SecureHash'] ?? null;

        // Loại bỏ 2 tham số không dùng khi hash
        unset($input['vnp_SecureHash']);
        unset($input['vnp_SecureHashType']);

        // Sắp xếp lại các tham số theo thứ tự A-Z
        ksort($input);

        // Tạo lại chuỗi dữ liệu hash
        $hashData = '';
        foreach ($input as $key => $value) {
            $hashData .= $key . '=' . $value . '&';
        }
        $hashData = rtrim($hashData, '&');

        // Tính lại chữ ký
        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);

        // So sánh chữ ký
        if ($secureHash === $vnp_SecureHash) {
            if ($request->vnp_ResponseCode === "00") {
                return response()->json(["message" => "Giao dịch thành công"]);
            } else {
                return response()->json(["message" => "Giao dịch thất bại"]);
            }
        } else {
            return response()->json(["message" => "Chữ ký không hợp lệ"]);
        }
    }
}
