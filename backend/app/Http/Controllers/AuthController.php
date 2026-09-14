<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\HandlesImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use HandlesImage;
    public function register(Request $request){
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
        $validated['status'] = 'inActive';
        $user = User::create($validated);
        return response()->json([
            'success' => true,
            'message' => 'Account created successfully. Please wait for admin approval before signing in.',
            'user' => $user
        ], 201);
    }
    public function login(Request $request){
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        if(!Auth::attempt($validated)){
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }
        $user = Auth::user();
        if ($user->status !== 'active') {
            Auth::logout();
            return response()->json([
                'success' => false,
                'message' => 'Your account is pending admin approval. You cannot sign in yet.'
            ], 403);
        }
        $token = $user->createToken('device-name')->plainTextToken;
        return response()->json([
            'success' => true,
            'message' => 'Login successfully',
            'user' => $user,
            'token' => $token
        ]);
    }
    public function logout(Request $request){
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'message' => 'Logout successfully'
        ]);
    }
    public function profile(Request $request){
        return response()->json([
            'success' => true,
            'user' => $request->user()
        ]);
    }
    public function updateProfile(Request $request){
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'gender' => 'sometimes|in:male,female',
            'phone' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        if (isset($validated['current_password']) && !Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.'
            ], 422);
        }
        if ($request->hasFile('avatar')) {
            $this->deleteImageIfStored($user->getRawOriginal('avatar'));
            $validated['avatar'] = $this->storeImage($request->file('avatar'), 'users');
        }
        if ($request->boolean('remove_avatar') && !$request->hasFile('avatar')) {
            $this->deleteImageIfStored($user->getRawOriginal('avatar'));
            $validated['avatar'] = null;
        }
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }
        unset($validated['current_password']);
        $user->update($validated);
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
}
