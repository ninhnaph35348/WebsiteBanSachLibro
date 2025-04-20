<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = Voucher::all();
        return response()->json($vouchers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'code' => 'required|string|unique:vouchers,code|max:50',
                'discount' => 'required|numeric',
                'discount_type' => 'required|string|in:percent,fixed',
                'max_discount' => 'nullable|numeric',
                'min_order_value' => 'required|numeric',
                'quantity' => 'required|integer|min:1',
                'used' => 'integer|min:0',
                'max_usage_per_user' => 'required|integer|min:1',
                'status' => 'required|boolean',
                'valid_from' => 'required|date',
                'valid_to' => 'required|date|after_or_equal:valid_from',
            ]);

            $voucher = Voucher::create($request->all());

            return response()->json([
                'message' => 'Thêm mới voucher thành công',
                'voucher' => $voucher
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $code)
    {
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return response()->json(['message' => 'Voucher không tồn tại'], 404);
        }
        return response()->json($voucher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $code)
    {
        try {
            $voucher = Voucher::where('code', $code)->first();

            if (!$voucher) {
                return response()->json(['message' => 'Voucher không tồn tại'], 404);
            }

            $request->validate([
                'code' => 'string|unique:vouchers,code,' . $voucher->id . '|max:50', // Chỉ kiểm tra duy nhất nếu mã voucher đã thay đổi
                'discount' => 'numeric',
                'discount_type' => 'string|in:percent,fixed',
                'max_discount' => 'nullable|numeric',
                'min_order_value' => 'numeric',
                'quantity' => 'integer|min:1',
                'used' => 'integer|min:0',
                'max_usage_per_user' => 'integer|min:1',
                'status' => 'boolean',
                'valid_from' => 'date',
                'valid_to' => 'date|after_or_equal:valid_from',
            ]);

            $voucher->update($request->all());

            return response()->json([
                'message' => 'Cập nhật voucher thành công',
                'voucher' => $voucher
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $code)
    {
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return response()->json(['message' => 'Voucher không tồn tại'], 404);
        }

        $newStatus = $voucher->status == 1 ? 0 : 1;
        $voucher->update(['status' => $newStatus]);

        $statusText = $newStatus == 1 ? 'Ngừng hoạt động' : 'Hoạt động';

        return response()->json(['message' => "Voucher $statusText"]);
    }
}
