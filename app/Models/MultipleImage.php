<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MultipleImage extends Model
{
    use HasFactory;

    protected $table = 'multiple_images';

    protected $fillable = ['image_link', 'product_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
