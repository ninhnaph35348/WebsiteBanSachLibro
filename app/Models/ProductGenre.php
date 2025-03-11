<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGenre extends Model
{
    use HasFactory;

    protected $table = 'product_genre';

    protected $fillable = [
        'product_id',
        'genre_id'
    ];

    // public function products()
    // {
    //     return $this->hasMany(Product::class);
    // }
    // public function genres()
    // {
    //     return $this->hasMany(Genre::class);
    // }
}
