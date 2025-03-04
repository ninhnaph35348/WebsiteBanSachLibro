<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $table = 'vouchers'; // Đảm bảo đúng tên bảng
    protected $fillable = ['code', 'discount', 'expiration_date']; // Thêm các cột cần thiết
}
