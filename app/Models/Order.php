<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    //
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
    ];

    // Liên kết với các bảng khác
    public function status()
    {
        return $this->belongsTo(OrderStatus::class, 'order_status_id');
    }

    public function payment()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_id');
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
