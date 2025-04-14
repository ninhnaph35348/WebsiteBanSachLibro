<?php

// app/Http/Controllers/VnpayController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VnpayController extends Controller
{
    private $vnp_TmnCode = "JJINZW3F";
    private $vnp_HashSecret = "WUB93OSEY5GUUVEHYTPSWEIM0TWNMJRM";
    private $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
    private $vnp_ReturnUrl = "http://localhost:5173/vnpay-return";

    public function createPayment(Request $request)
    {
        $vnp_TxnRef = time();
        $vnp_OrderInfo = $request->input('orderInfo');
        $vnp_Amount = $request->input('amount') * 100; // VNPay cần nhân 100
        $vnp_BankCode = $request->input('bankCode', '');
        $vnp_Locale = $request->input('language', 'vn');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $this->vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => now()->format('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => '127.0.0.1',
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $this->vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        if ($vnp_BankCode != "") {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = "";
        $i = 0;
        $hashdata = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . "=" . urlencode($value) . '&';
        }

        $vnp_Url = $this->vnp_Url . "?" . $query;
        if (isset($this->vnp_HashSecret)) {
            $vnpSecureHash =   hash_hmac('sha512', $hashdata, $this->vnp_HashSecret); //
            $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
        }

        return response()->json(['payment_url' => $vnp_Url]);
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_SecureHash = $request->query('vnp_SecureHash');
        $inputData = $request->query();
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        ksort($inputData);
        $hashData = "";
        foreach ($inputData as $key => $value) {
            $hashData .= $key . "=" . $value . "&";
        }
        $hashData = rtrim($hashData, "&");
        $secureHash = hash_hmac('sha512', $hashData, $this->vnp_HashSecret);

        if ($secureHash === $vnp_SecureHash) {
            if ($inputData['vnp_ResponseCode'] == '00') {
                return response()->json(['message' => 'Giao dịch thành công']);
            } else {
                return response()->json(['message' => 'Giao dịch thất bại']);
            }
        } else {
            return response()->json(['message' => 'Chữ ký không hợp lệ'], 400);
        }
    }

    // Optional: IPN từ VNPay (nếu bật trong cấu hình Merchant)
    public function vnpayIPN(Request $request)
    {
        // Giống logic xác thực hash như trên
        // Xử lý lưu DB khi có IPN về
        return response('IPN received');
    }
}
