<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    use HasFactory;

    protected $table = 'genres';

    protected $fillable = [
        'name',
        'del_flg',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_genres');
    }

    // public function productGenre()
    // {
    //     return $this->hasMany(ProductGenre::class);
    // }
}
