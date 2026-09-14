<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->endOfMonth()->toDateString());

        $orders = Order::whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])->get();

        $totalOrders = $orders->count();
        $totalRevenue = $orders->where('status', 'confirmed')->sum('total');
        $pendingOrders = $orders->where('status', 'pending')->count();
        $confirmedOrders = $orders->where('status', 'confirmed')->count();
        $averageOrderValue = $confirmedOrders > 0 ? $totalRevenue / $confirmedOrders : 0;

        $ordersByType = [
            'dine_in' => $orders->where('order_type', 'dine_in')->count(),
            'takeaway' => $orders->where('order_type', 'takeaway')->count(),
        ];

        $dailyRevenue = $orders->where('status', 'confirmed')
            ->groupBy(fn ($order) => $order->created_at->format('Y-m-d'))
            ->map(fn ($group) => $group->sum('total'))
            ->sortKeys()
            ->toArray();

        $topFoods = $orders->where('status', 'confirmed')
            ->flatMap->items
            ->groupBy('food_id')
            ->map(fn ($items) => [
                'food' => $items->first()->food,
                'total_quantity' => $items->sum('quantity'),
                'total_revenue' => $items->sum('subtotal'),
            ])
            ->sortByDesc('total_quantity')
            ->take(10)
            ->values();

        $paymentMethods = Payment::where('status', 'paid')
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('payment_method')
            ->get();

        return response()->json([
            'success' => true,
            'report' => [
                'period' => ['from' => $from, 'to' => $to],
                'total_orders' => $totalOrders,
                'total_revenue' => number_format($totalRevenue, 2, '.', ''),
                'pending_orders' => $pendingOrders,
                'confirmed_orders' => $confirmedOrders,
                'average_order_value' => number_format($averageOrderValue, 2, '.', ''),
                'orders_by_type' => $ordersByType,
                'daily_revenue' => $dailyRevenue,
                'top_foods' => $topFoods,
                'payment_methods' => $paymentMethods,
            ]
        ]);
    }
}
