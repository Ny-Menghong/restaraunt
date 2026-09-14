<?php

namespace App\Http\Controllers;

use App\Models\UserRequest;
use Illuminate\Http\Request;

class UserRequestController extends Controller
{
    public function index()
    {
        $userRequests = UserRequest::with('user')->latest()->get();
        return response()->json([
            'success' => true,
            'user_requests' => $userRequests
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'request_qty' => 'required|integer|min:1',
            'status' => 'sometimes|in:pending,comfirm,cancle',
        ]);

        if (!isset($validated['status'])) {
            $validated['status'] = 'pending';
        }

        $userRequest = UserRequest::create($validated);
        $userRequest->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Request created successfully',
            'user_request' => $userRequest
        ], 201);
    }

    public function show(UserRequest $userRequest)
    {
        $userRequest->load('user');
        return response()->json([
            'success' => true,
            'user_request' => $userRequest
        ]);
    }

    public function update(Request $request, UserRequest $userRequest)
    {
        $validated = $request->validate([
            'request_qty' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:pending,comfirm,cancle',
        ]);

        $userRequest->update($validated);
        $userRequest->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Request updated successfully',
            'user_request' => $userRequest
        ]);
    }

    public function destroy(UserRequest $userRequest)
    {
        $userRequest->delete();
        return response()->json([
            'success' => true,
            'message' => 'Request deleted successfully'
        ]);
    }
}
