<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
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
        Order::where('id', $validated['order_id'])->update(['status' => 'completed']);
        return response()->json([
            'success' => true,
            'message' => 'Payment successful',
            'payment' => $payment,
        ]);
    }
}
