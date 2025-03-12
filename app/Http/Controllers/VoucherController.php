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
        $vouchers = Voucher::all()->where('del_flg', 0);
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
    public function show(string $id)
    {
        $voucher = Voucher::find($id);
        if (!$voucher) {
            return response()->json(['message' => 'Voucher không tồn tại'], 404);
        }
        return response()->json($voucher);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $voucher = Voucher::find($id);
            if (!$voucher) {
                return response()->json(['message' => 'Voucher không tồn tại'], 404);
            }

            $request->validate([
                'code' => 'string|unique:vouchers,code|max:50',
                'discount' => 'numeric',
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
    public function destroy(string $id)
    {
        $voucher = Voucher::find($id);
        if (!$voucher) {
            return response()->json(['message' => 'Voucher không tồn tại'], 404);
        }

        // $voucher->delete();
        $voucher->update(['del_flg' => 1]);

        return response()->json(['message' => 'Voucher đã được xóa thành công']);
    }
}
