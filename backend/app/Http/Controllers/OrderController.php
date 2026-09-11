<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('items.food', 'table', 'payment', 'customer');

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }
        if ($request->has('order_type') && $request->order_type !== '') {
            $query->where('order_type', $request->order_type);
        }

        $orders = $query->latest()->get();
        return response()->json([
            'success' => true,
            'orders' => $orders
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'table_id' => [
                    'nullable',
                    'exists:tables,id',
                ],
                'customer_id' => [
                    'nullable',
                    'exists:customers,id',
                ],
                'items' => [
                    'required',
                    'array',
                    'min:1',
                ],
                'items.*.food_id' => [
                    'required',
                    'exists:foods,id',
                ],
                'items.*.quantity' => [
                    'required',
                    'integer',
                    'min:1',
                ],
                'order_type' => [
                    'required',
                    'in:dine_in,takeaway',
                ],
                'payment_method' => [
                    'nullable',
                    'in:cash,aba,acleda,bakong',
                ],
                'discount' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
            ]);
            if ($validated['order_type'] === 'dine_in' && empty($validated['table_id'])) {
                return response()->json([
                    'message' => 'Table is required for dine-in order.'
                ], 422);
            }
            $order = DB::transaction(function () use ($validated) {
                $order = Order::create([
                    'order_number' => 'ORD-' . now()->format('YmdHis') . '-' . rand(100, 999),
                    'order_type' => $validated['order_type'],
                    'table_id' => $validated['table_id'] ?? null,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'status' => 'pending',
                    'subtotal' => 0,
                    'discount' => $validated['discount'] ?? 0,
                    'total' => 0,
                ]);
                $total = 0;
                foreach ($validated['items'] as $item) {
                    $food = Food::findOrFail($item['food_id']);
                    $price = $food->price;
                    $subtotal = $price * $item['quantity'];
                    $order->items()->create([
                        'food_id' => $food->id,
                        'quantity' => $item['quantity'],
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ]);
                    $total += $subtotal;
                }
                $discount = $validated['discount'] ?? 0;
                $order->update([
                    'subtotal' => $total,
                    'total' => $total - $discount,
                ]);
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $total - $discount,
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'status' => 'pending',
                ]);
                return $order;
            });
            $order->load('items.food', 'table', 'payment', 'customer');
            return response()->json([
                'message' => 'Order created successfully.',
                'data' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order creation failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(Order $order)
    {
        return response()->json([
            'success' => true,
            'order' => $order->load('items.food', 'table', 'payment', 'customer')
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,preparing,ready,completed,cancelled',
            'discount' => 'nullable|numeric|min:0',
        ]);
        if (isset($validated['discount'])) {
            $validated['total'] = $order->subtotal - $validated['discount'];
            if ($validated['total'] < 0) {
                $validated['total'] = 0;
            }
        }
        $order->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Order updated successfully',
            'order' => $order->load('items.food', 'table', 'payment', 'customer')
        ]);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json([
            'success' => true,
            'message' => 'Order deleted successfully'
        ]);
    }
}
