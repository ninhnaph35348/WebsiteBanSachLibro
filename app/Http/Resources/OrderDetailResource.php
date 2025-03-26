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
            'order_id' => $this->order_id ? [
                'code_order' => $this->order->code_order,
                'order_status' => $this->order->status ? $this->order->status->name : null,
                'user' => $this->order->user ? $this->order->user->name : null,
                'total' => $this->order->total,
                'voucher' => $this->order->voucher ? $this->order->voucher->code : null,
                'created_at' => $this->order->created_at,
            ] : null,
            'product_variant_id' => $this->product_variant_id ? [
                'quantity' => $this->productVariant->quantity,
                'price' => $this->productVariant->price,
                'promotion' => $this->productVariant->promotion,
                'cover' => $this->productVariant->cover ? $this->productVariant->cover->type : null,
                'product' => $this->productVariant->product ? [
                    'code' => $this->productVariant->product->code,
                    'title' => $this->productVariant->product->title,
                    'image' => $this->productVariant->product->image,
                ] : null,
            ] : null,
            'quantity' => $this->quantity,
            'price' => $this->price,
        ];
    }
}
