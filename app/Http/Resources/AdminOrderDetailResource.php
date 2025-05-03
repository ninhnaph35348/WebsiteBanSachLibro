<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminOrderDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cover' => $this->hard_cover,
            'code' => $this->productVariant->product->code,
            'title' => $this->hard_products,
            'image' => $this->productVariant->product->image,
            'quantity' => $this->quantity,
            'price' => $this->hard_price_time,
            'total_line' => $this->quantity * $this->productVariant->promotion ?? $this->productVariant->price,
        ];
    }
}
