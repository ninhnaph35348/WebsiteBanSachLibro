<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use HasFactory;

    protected $table = 'authors';

    protected $fillable = [
        'name',
        'del_flg'
    ];
 
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
