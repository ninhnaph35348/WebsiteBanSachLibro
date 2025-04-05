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
            'code' => $this->productVariant->product->code,
            'title' => $this->productVariant->product->title,
            'price_product' => $this->productVariant->promotion ?? $this->productVariant->price,    
            'image' => $this->productVariant->product->image,
            'quantity' => $this->quantity,
            'total_line' => $this->quantity * $this->productVariant->promotion ?? $this->productVariant->price,
        ];
    }
}
