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
            'code' => $this->product ? $this->product->code : null,
            'rating' => $this->rating,
            'review' => $this->review,
            'status' => $this->status,
            'title' => $this->product ? $this->product->title : null,
            'username' => $this->user ? $this->user->username : null,
            'created_at' => $this->created_at->format('H:i d/m/Y'),
        ];
    }
}
