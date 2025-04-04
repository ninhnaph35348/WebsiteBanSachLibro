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
            'order_id' => [
                'code_order' => $this->order->code_order,
                'order_status' => $this->order->status ? $this->order->status->name : null,
                'total' => $this->order->total,
                'created_at' => $this->order->created_at->format('Y-m-d H:i:s'),
            ],
            'product_variant' => [
                'quantity' => $this->productVariant->quantity,
                'price' => $this->productVariant->price,
                'promotion' => $this->productVariant->promotion,
                'product' => $this->productVariant->product ? [
                    'code' => $this->productVariant->product->code,
                    'title' => $this->productVariant->product->title,
                    'image' => $this->productVariant->product->image,
                ] : null,
            ],
            'quantity' => $this->quantity,
            'total_line' => $this->total_line,
        ];
    }
}
