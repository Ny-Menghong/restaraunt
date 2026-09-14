<?php

namespace App\Http\Controllers;

use App\Models\Food;
use App\Traits\HandlesImage;
use Illuminate\Http\Request;

class FoodController extends Controller
{
    use HandlesImage;

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'price' => 'required|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);
        if ($request->hasFile('image')) {
            $validated['image'] = $this->storeImage($request->file('image'), 'foods');
        }
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'price' => 'sometimes|numeric|min:0',
            'quantity' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);
        if ($request->hasFile('image')) {
            $this->deleteImageIfStored($food->getRawOriginal('image'));
            $validated['image'] = $this->storeImage($request->file('image'), 'foods');
        }
        if ($request->has('remove_image')) {
            $this->deleteImageIfStored($food->getRawOriginal('image'));
            $validated['image'] = null;
        }
        $food->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Food updated successfully',
            'food' => $food->load('category')
        ]);
    }

    public function destroy(Food $food)
    {
        $this->deleteImageIfStored($food->getRawOriginal('image'));
        $food->delete();
        return response()->json([
            'success' => true,
            'message' => 'Food deleted successfully'
        ]);
    }
}
