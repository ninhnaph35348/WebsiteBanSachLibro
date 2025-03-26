<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code_order' => $this->code_order,
            'total_price' => $this->total_price,
            'note' => $this->note,
            'payment_method' => $this->payment_method,
            'user_name' => $this->user_name,
            'user_email' => $this->user_email,
            'user_phone' => $this->user_phone,
            'user_address' => $this->user_address,
            'status' => $this->status ? $this->status->name : null,
            'voucher' => $this->voucher ? $this->voucher->code : null,
            'user' => $this->user ? $this->user->username : null,
            // 'order_details' => OrderDetailResource::collection($this->orderDetails),
        ];
    }
}
