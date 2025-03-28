<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'cover' => $this->cover ? $this->cover->type : null,
            'product' => $this->product ? [
                'code' => $this->product->code,
                'title' => $this->product->title,
                'author' => $this->product->author ? $this->product->author->name : null,
                'publisher' => $this->product->publisher ? $this->product->publisher->name : null,
                'language' => $this->product->language ? $this->product->language->name : null,
                'category' => $this->product->category ? $this->product->category->name : null,
                'genres' => $this->product->genres ? $this->product->genres->pluck('name') : null,
                'image' => $this->product->image,
                'images' => $this->product->images ? $this->product->images->pluck('image_link') : null,
            ] : null,
        ];
    }
}
