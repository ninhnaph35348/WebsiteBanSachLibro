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
            'order_id' => $this->order_id ? [
                'code_order' => $this->order->code_order,
                'order_status' => $this->order->status ? $this->order->status->name : null,
                'user' => $this->order->user ? $this->order->user->username : null,
                'total' => $this->order->total_price,
                'voucher' => $this->order->voucher ? $this->order->voucher->code : null,
                'user_name' => $this->order->user_name ? $this->order->user_name : null,
                'user_email' => $this->order->user_email ? $this->order->user_email : null,
                'user_phone' => $this->order->user_phone ? $this->order->user_phone : null,
                'user_address' => $this->order->user_address ? $this->order->user_address : null,
                'shipping_name' => $this->order->shipping_name ? $this->order->shipping_name : null,
                'shipping_phone' => $this->order->shipping_phone ? $this->order->shipping_phone : null,
                'shipping_address' => $this->order->shipping_address ? $this->order->shipping_address : null,
                'note' => $this->order->note ? $this->order->note : null,
                'created_at' => $this->order->created_at ? $this->order->created_at->format('Y-m-d H:i:s') : null,
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
            'total_line' => $this->quantity * $this->productVariant->price,
        ];
    }
}
