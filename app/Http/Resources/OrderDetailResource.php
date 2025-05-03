<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cover' => $this->hard_cover,
            'code' => $this->productVariant->product->code,
            'title' => $this->hard_products,
            'price_product' => $this->hard_price_time,
            'image' => $this->productVariant->product->image,
            'quantity' => $this->quantity,
            'total_line' => $this->quantity * ($this->productVariant->promotion !== null ? $this->productVariant->promotion : $this->productVariant->price),
        ];
    }
}
