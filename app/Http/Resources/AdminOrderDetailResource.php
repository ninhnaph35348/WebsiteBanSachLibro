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
            'cover' => $this->productVariant->cover ? $this->productVariant->cover->type : null,
            'code' => $this->productVariant->product->code,
            'title' => $this->productVariant->product->title,
            'image' => $this->productVariant->product->image,
            'quantity' => $this->quantity,
            'price' => $this->productVariant->promotion ?? $this->productVariant->price,
            'total_line' => $this->quantity * $this->productVariant->promotion ?? $this->productVariant->price,
        ];
    }
}
