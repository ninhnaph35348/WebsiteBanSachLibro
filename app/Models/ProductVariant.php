<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;


    protected $table = 'product_variants';
    protected $fillable = [
        'product_id',
        'quantity',
        'price',
        'promotion',
        'del_flg',
        'cover_id',
        'promotion'
    ];

    public function cover()
    {
        return $this->belongsTo(Cover::class, 'cover_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
