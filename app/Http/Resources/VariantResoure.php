<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class VariantResoure extends JsonResource
{
    public function toArray(Request $request): array
    {
        // dd($this->image);
        return [
            'id' => $this->id,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'promotion' => $this->promotion,
            'cover_id' => $this->cover ? $this->cover->id : null,
            'cover' => $this->cover ? $this->cover->type : null,
            'sold_quantity' => $this->soldQuantity($this->product->code),
            'del_flg' => $this->del_flg,
            'product' => $this->product ? [
                'id' => $this->product->id,
                'code' => $this->product->code,
                'title' => $this->product->title,
                'status' => $this->product->status,
                'author' => $this->product->author ? $this->product->author->name : null,
                'publisher' => $this->product->publisher ? $this->product->publisher->name : null,
                'description' => $this->product->description ? $this->product->description : null,
                'rating' => round($this->product->reviews->avg('rating'), 1),
                'published_year' => $this->product->published_year ? $this->product->published_year : null,
                'book_count' => $this->product->book_count ? $this->product->book_count : null,
                'supplier_name' => $this->product->supplier_name ? $this->product->supplier_name : null,
                'language' => $this->product->language ? $this->product->language->name : null,
                'category' => $this->product->category ? $this->product->category->name : null,
                'genres' => $this->product->genres ? $this->product->genres->pluck('name') : null,
                'image' => $this->product->image,
                'images' => $this->product->images ? $this->product->images->pluck('image_link') : null,
            ] : null,
        ];
    }

    public function soldQuantity(string $code)
    {
        return DB::table('order_details')
            ->join('product_variants', 'order_details.product_variant_id', '=', 'product_variants.id')
            ->join('products', 'product_variants.product_id', '=', 'products.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('products.code', $code)
            ->where('orders.order_status_id', 6) // 6 là trạng thái "đã hoàn tất"
            ->sum('order_details.quantity');
    }
}
