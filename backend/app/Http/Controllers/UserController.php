<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'sometimes|in:admin,manager,cashier',
        ]);
        if (empty($validated['role'])) {
            $validated['role'] = 'cashier';
        }
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'user' => $user
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'gender' => 'sometimes|in:male,female',
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|string',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:6',
            'role' => 'sometimes|in:admin,manager,cashier',
        ]);
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        $user->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }
}
