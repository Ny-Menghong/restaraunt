<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('products')->get();
        return response()->json($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $category = Category::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    public function show(Category $category)
    {
        return response()->json([
            'success' => true,
            'category' => $category->load('products')
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug,' . $category->id,
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);
        if (isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['slug']);
        }
        $category->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'category' => $category
        ]);
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully'
        ]);
    }
}
