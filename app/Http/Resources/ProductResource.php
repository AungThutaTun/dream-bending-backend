<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'origin'      => $this->origin,
            'price'       => $this->price,
            'stock'       => $this->stock,
            'in_stock'    => $this->isInStock(),
            'thumbnail'   => $this->thumbnail,
            'badge'       => $this->badge,
            'is_active'   => $this->is_active,
            'category'    => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'icon' => $this->category->icon,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
