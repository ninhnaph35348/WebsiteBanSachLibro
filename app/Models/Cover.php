<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cover extends Model
{
    use HasFactory;

    protected $table = 'covers';
    protected $fillable = [
        'name',
        'del_flg'
    ];


    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'cover_id');
    }
}
