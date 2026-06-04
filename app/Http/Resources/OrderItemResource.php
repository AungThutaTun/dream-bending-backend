<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'       => $this->id,
            'price'    => $this->price,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'product'  => $this->whenLoaded('product', fn () => [
                'id'        => $this->product->id,
                'name'      => $this->product->name,
                'thumbnail' => $this->product->thumbnail,
                'slug'      => $this->product->slug,
            ]),
        ];
    }
}
