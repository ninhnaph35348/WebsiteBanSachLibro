<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'code_order',
        'total_price',
        'note',
        'order_status_id',
        'payment_method',
        'voucher_id',
        'user_id',
        'user_name',
        'user_email',
        'user_phone',
        'user_address',
        'shipping_name',
        'shipping_phone',
        'shipping_address',
        'shipping_email'
    ];

    // Liên kết với OrderStatus
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    // Liên kết với Voucher
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    // Liên kết với User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Liên kết với OrderDetails (giả sử có bảng chứa chi tiết đơn hàng)
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }
}
