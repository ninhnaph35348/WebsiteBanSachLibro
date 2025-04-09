<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'code',
        'title',
        'image',
        'supplier_name',
        'published_year',
        'book_count',
        'author_id',
        'publisher_id',
        'description',
        'language_id',
        'category_id',
        'del_flg',
        'status'
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id'); // Chú ý: "language_id" sai chính tả trong DB
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'product_genres', 'product_id', 'genre_id');
    }

    public function images()
    {
        return $this->hasMany(MultipleImage::class, 'product_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id');
    }
}
