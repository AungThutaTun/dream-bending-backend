<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Get current user's wishlist.
     */
    public function index(Request $request): JsonResponse
    {
        $wishlist = Wishlist::with('product.category')
            ->where('user_id', $request->user()->id)
            ->get()
            ->pluck('product');

        return $this->success(ProductResource::collection($wishlist));
    }

    /**
     * Toggle product in wishlist (add if not exists, remove if exists).
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $existing = Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return $this->success(['wishlisted' => false], 'Removed from wishlist');
        }

        Wishlist::create([
            'user_id'    => $request->user()->id,
            'product_id' => $request->product_id,
        ]);

        return $this->success(['wishlisted' => true], 'Added to wishlist');
    }
}
