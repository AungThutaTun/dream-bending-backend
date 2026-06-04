<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get current user's cart.
     */
    public function index(Request $request): JsonResponse
    {
        $cart = Cart::with('product.category')
            ->where('user_id', $request->user()->id)
            ->get();

        $subtotal = $cart->sum(fn ($item) => $item->product->price * $item->quantity);
        $shippingFee = $subtotal >= 50000 ? 0 : 3000;
        $total = $subtotal + $shippingFee;

        return $this->success([
            'items'        => CartResource::collection($cart),
            'subtotal'     => $subtotal,
            'shipping_fee' => $shippingFee,
            'total'        => $total,
            'free_shipping'=> $subtotal >= 50000,
        ]);
    }

    /**
     * Add item to cart.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return $this->error("Only {$product->stock} items available in stock.", 422);
        }

        $cartItem = Cart::updateOrCreate(
            ['user_id' => $request->user()->id, 'product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        return $this->success(new CartResource($cartItem->load('product')), 'Added to cart');
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, Cart $cart): JsonResponse
    {
        if ($cart->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($cart->product->stock < $request->quantity) {
            return $this->error("Only {$cart->product->stock} items available in stock.", 422);
        }

        $cart->update(['quantity' => $request->quantity]);

        return $this->success(new CartResource($cart->load('product')), 'Cart updated');
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, Cart $cart): JsonResponse
    {
        if ($cart->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        $cart->delete();

        return $this->success(message: 'Removed from cart');
    }

    /**
     * Clear all cart items for the user.
     */
    public function clear(Request $request): JsonResponse
    {
        Cart::where('user_id', $request->user()->id)->delete();

        return $this->success(message: 'Cart cleared');
    }
}
