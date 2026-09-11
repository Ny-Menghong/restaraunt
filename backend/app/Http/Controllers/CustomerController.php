<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|unique:customers,email',
            'address' => 'nullable|string',
            'note' => 'nullable|string',
        ]);
        $customer = Customer::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'Customer created successfully',
            'customer' => $customer
        ], 201);
    }

    public function show(Customer $customer)
    {
        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string',
            'note' => 'nullable|string',
        ]);
        $customer->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Customer updated successfully',
            'customer' => $customer
        ]);
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json([
            'success' => true,
            'message' => 'Customer deleted successfully'
        ]);
    }
}
