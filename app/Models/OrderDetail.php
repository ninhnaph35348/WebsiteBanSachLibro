<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'product_variant_id', // Đổi từ variant_id nếu cần
        'quantity',
        'total_line',
        'hard_products', // Lưu tên sản phẩm cứng
        'hard_price_time', // Lưu thời gian của giá cứng
    ];

    public $timestamps = false;


    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
