<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rating' => $this->rating,
            'review' => $this->review,
            'status' => $this->status,
            'title' => $this->product ? $this->product->title : null,

            'username' => $this->user ? $this->user->username : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'del_flg' => $this->del_flg,
        ];
    }
}
