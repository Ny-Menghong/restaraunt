<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(){
        $payments = Payment::with('order')->get();
        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }
    public function pay(Request $request){
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric',
            'payment_method' => 'required|in:cash,aba,acleda,bakong',
        ]);
        $payment = Payment::where('order_id', $validated['order_id'])->first();
        if ($payment) {
            $payment->update([
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'status' => 'paid',
            ]);
        } else {
            $payment = Payment::create([
                'order_id' => $validated['order_id'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'status' => 'paid',
            ]);
        }
        $order = Order::find($validated['order_id']);
        $order->update(['status' => 'confirmed']);
        if ($order->table_id) {
            $open = Order::where('table_id', $order->table_id)
                ->where('id', '!=', $order->id)
                ->where('status', 'pending')
                ->exists();
            if (!$open) {
                Table::where('id', $order->table_id)->update(['status' => 'active']);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'Payment successful',
            'payment' => $payment,
        ]);
    }
}
