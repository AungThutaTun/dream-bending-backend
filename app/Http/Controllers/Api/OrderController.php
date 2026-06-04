<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Get current user's orders.
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return $this->success([
            'orders'     => OrderResource::collection($orders),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    /**
     * Get a single order.
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()->id) {
            return $this->forbidden();
        }

        return $this->success(new OrderResource($order->load('items.product')));
    }

    /**
     * Place a new order from cart.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'payment_method'   => ['required', 'in:cod,kpay,wavepay'],
            'shipping_address' => ['required', 'string'],
            'shipping_city'    => ['required', 'string'],
            'payment_reference'=> ['nullable', 'string'],
            'promo_code'       => ['nullable', 'string'],
            'notes'            => ['nullable', 'string'],
        ]);

        $cartItems = Cart::with('product')
            ->where('user_id', $request->user()->id)
            ->get();

        if ($cartItems->isEmpty()) {
            return $this->error('Your cart is empty.', 422);
        }

        // Check stock
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return $this->error("'{$item->product->name}' has insufficient stock.", 422);
            }
        }

        // Calculate totals
        $subtotal = $cartItems->sum(fn ($item) => $item->product->price * $item->quantity);
        $shippingFee = $subtotal >= 50000 ? 0 : 3000;
        $discount = 0;

        // Promo code: DREAMBEND10 = 10% off first order
        if ($request->promo_code === 'DREAMBEND10') {
            $hasOrders = Order::where('user_id', $request->user()->id)->exists();
            if (!$hasOrders) {
                $discount = $subtotal * 0.10;
            }
        }

        $total = $subtotal + $shippingFee - $discount;

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_number'      => Order::generateOrderNumber(),
                'user_id'           => $request->user()->id,
                'status'            => 'pending',
                'subtotal'          => $subtotal,
                'shipping_fee'      => $shippingFee,
                'discount'          => $discount,
                'total'             => $total,
                'payment_method'    => $request->payment_method,
                'payment_reference' => $request->payment_reference,
                'shipping_address'  => $request->shipping_address,
                'shipping_city'     => $request->shipping_city,
                'promo_code'        => $request->promo_code,
                'notes'             => $request->notes,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'price'      => $item->product->price,
                    'quantity'   => $item->quantity,
                    'subtotal'   => $item->product->price * $item->quantity,
                ]);

                // Decrease stock
                $item->product->decrement('stock', $item->quantity);
            }

            // Clear cart
            Cart::where('user_id', $request->user()->id)->delete();

            DB::commit();

            return $this->created(
                new OrderResource($order->load('items.product')),
                'Order placed successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Failed to place order. Please try again.', 500);
        }
    }
}
