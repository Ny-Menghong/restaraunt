<?php

namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::withCount('orders')->get();
        return response()->json(
            [
                'success' => true,
                'message' => 'Tables retrieved successfully',
                'tables' => $tables
            ], 200
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|unique:tables,table_number',
            'capacity' => 'required|integer|min:1',
            'location' => 'required|string'
        ]);
        $validated['qr_token'] = Str::random(40);
        $table = Table::create($validated);
        return response()->json(
            [
                'success' => true,
                'message' => 'Table created successfully',
                'table' => $table
            ], 201
        );
    }

    public function show(Table $table)
    {
        return response()->json([
            'success' => true,
            'table' => $table->load('orders')
        ]);
    }

    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'table_number' => 'sometimes|string|unique:tables,table_number,' . $table->id,
            'capacity' => 'sometimes|integer|min:1',
            'location' => 'sometimes|string'
        ]);
        $table->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Table updated successfully',
            'table' => $table
        ]);
    }

    public function destroy(Table $table)
    {
        $table->delete();
        return response()->json([
            'success' => true,
            'message' => 'Table deleted successfully'
        ]);
    }
}
