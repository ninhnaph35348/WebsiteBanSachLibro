<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers';
    protected $fillable = [
        'code', // Mã voucher
        'discount', // Giá trị giảm giá
        'discount_type', // Loại giảm giá (percent hoặc fixed)
        'max_discount', // Giảm giá tối đa
        'min_order_value', // Giá trị đơn hàng tối thiểu để áp dụng voucher
        'quantity', // Số lượng voucher
        'used', // Số lượng đã sử dụng
        'max_usage_per_user', // Số lần sử dụng tối đa cho mỗi người dùng
        'valid_from', // Ngày bắt đầu hiệu lực
        'valid_to', // Ngày kết thúc hiệu lực
        'status', // Trạng thái (0: không hoạt động, 1: hoạt động)
        'del_flg' ,
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'voucher_user', 'voucher_id', 'user_id')
            ->withPivot('used_at', 'status') // Các trường bổ sung trong bảng trung gian
            ->withTimestamps();
    }
}
