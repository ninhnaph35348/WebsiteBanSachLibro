<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items'; // Đảm bảo đúng tên bảng
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price']; // Các cột có thể nhập liệu

    // Quan hệ với bảng orders
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Quan hệ với bảng products
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
