<?php

namespace App\Http\Controllers;

use App\Models\Food;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    public function index()
    {
        $foods = Food::with('category')->get();
        return response()->json(
            [
                'success' => true,
                'foods' => $foods
            ]
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'image' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);
        $food = Food::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'Food created successfully',
            'food' => $food->load('category')
        ], 201);
    }

    public function show(Food $food)
    {
        return response()->json([
            'success' => true,
            'food' => $food->load('category')
        ]);
    }

    public function update(Request $request, Food $food)
    {
        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'image' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);
        $food->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Food updated successfully',
            'food' => $food->load('category')
        ]);
    }

    public function destroy(Food $food)
    {
        $food->delete();
        return response()->json([
            'success' => true,
            'message' => 'Food deleted successfully'
        ]);
    }
}
