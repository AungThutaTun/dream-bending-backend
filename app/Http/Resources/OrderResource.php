<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'order_number'      => $this->order_number,
            'status'            => $this->status,
            'subtotal'          => $this->subtotal,
            'shipping_fee'      => $this->shipping_fee,
            'discount'          => $this->discount,
            'total'             => $this->total,
            'payment_method'    => $this->payment_method,
            'payment_reference' => $this->payment_reference,
            'shipping_address'  => $this->shipping_address,
            'shipping_city'     => $this->shipping_city,
            'promo_code'        => $this->promo_code,
            'notes'             => $this->notes,
            'items'             => OrderItemResource::collection($this->whenLoaded('items')),
            'user'              => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
