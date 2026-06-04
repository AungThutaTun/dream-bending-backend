<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * List all orders with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', fn ($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $orders = $query->latest()->paginate(15);

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
    public function show(Order $order): JsonResponse
    {
        return $this->success(new OrderResource($order->load(['user', 'items.product'])));
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status'            => ['required', 'in:pending,confirmed,processing,shipped,delivered,cancelled'],
            'payment_reference' => ['nullable', 'string'],
        ]);

        $order->update([
            'status'            => $request->status,
            'payment_reference' => $request->payment_reference ?? $order->payment_reference,
        ]);

        return $this->success(new OrderResource($order->load(['user', 'items.product'])), 'Order status updated');
    }

    /**
     * Dashboard statistics.
     */
    public function dashboard(): JsonResponse
    {
        $totalRevenue = Order::whereNotIn('status', ['cancelled'])->sum('total');
        $totalOrders  = Order::count();
        $totalUsers   = User::where('role', 'customer')->count();
        $totalProducts = Product::where('is_active', true)->count();

        $recentOrders = Order::with(['user', 'items'])
            ->latest()
            ->take(5)
            ->get();

        $ordersByStatus = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $monthlyRevenue = Order::whereNotIn('status', ['cancelled'])
            ->selectRaw("strftime('%Y-%m', created_at) as month, sum(total) as revenue")
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(6)
            ->get();

        return $this->success([
            'stats' => [
                'total_revenue'  => $totalRevenue,
                'total_orders'   => $totalOrders,
                'total_users'    => $totalUsers,
                'total_products' => $totalProducts,
            ],
            'recent_orders'   => OrderResource::collection($recentOrders),
            'orders_by_status'=> $ordersByStatus,
            'monthly_revenue' => $monthlyRevenue,
        ]);
    }
}
