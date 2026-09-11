<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Food;
use App\Models\Table;
use Illuminate\Http\Request;

class QRMenuController extends Controller
{
    public function show(Table $table)
    {
        $foods = Food::with('category')->get();
        $categories = Category::all();
        return response()->json([
            'success' => true,
            'table' => $table,
            'foods' => $foods,
            'categories' => $categories,
        ]);
    }
}
