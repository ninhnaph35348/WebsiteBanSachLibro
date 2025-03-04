<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'image' => $this->image,
            'supplier_name' => $this->supplier_name,
            'description' => $this->description,
            'status' => $this->status,
            'author' => $this->author ? $this->author->name : null,
            'publisher' => $this->publisher ? $this->publisher->name : null,
            'language' => $this->language ? $this->language->name : null,
            'category' => $this->category ? $this->category->name : null,
            'genres' => $this->genres->pluck('name'), // Lấy danh sách tên genre
            'images' => $this->images->pluck('image_link'),
        ];
    }
}
