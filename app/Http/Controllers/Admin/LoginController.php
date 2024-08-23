<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Log;


class LoginController extends Controller
{
    //? Admin login API
    public function login(Request $request)
    {
        // Define validation rules
        $rules = [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];

        // Validate the request
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'code' => 422,
                'errors' => $validator->errors(),
                'data' => [],
            ], 422);
        }

        // Extract credentials
        $credentials = $request->only('email', 'password');

        // Attempt to authenticate the user
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
                'code' => 401,
                'data' => [],
            ], 401);
        }

        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is an admin
        if ($user->hasRole('Admin')) {
            $token = $user->createToken('adminToken')->accessToken;

            return response()->json([
                'message' => 'Login successful!',
                'code' => 200,
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 200);
        }

        // If the user is not an admin
        return response()->json([
            'message' => 'User is not an admin.',
            'code' => 403,
            'data' => [],
        ], 403);
    }

    //? logout API
    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'error' => 'User not found.',
                    'code' => 404
                ], 404);
            }

            // Revoke all of the user's tokens
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Logged out successfully.',
                'code' => 200
            ], 200);
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Internal Server Error! ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
    public function adminProfile()
    {
        try {

            $admin = auth()->user();

            if (!$admin) {
                return response()->json([
                    'message' => 'Admin not found',
                    'code' => 404,
                    'data' => [],
                ], 404);
            }

            return response()->json([
                'message' => 'Successfully fetched admin profile',
                'code' => 200,
                'data' => $admin,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching the admin profile.',
                'code' => 500,
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function Pro(Request $request)
    {
        try {
            $perPage = 10;
            $currentPage = $request->input('page', 1);

            $users = User::whereIn('type', [2, 3])
                ->paginate($perPage, ['*'], 'page', $currentPage);

            return response()->json([
                'msg' => 'Users fetched successfully!',
                'code' => 200,
                'data' => [
                    'users' => $users->items(),
                    'pagination' => [
                        'total' => $users->total(),
                        'current_page' => $users->currentPage(),
                        'last_page' => $users->lastPage(),
                        'per_page' => $users->perPage(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'An unexpected error occurred',
                'code' => 500,
                'data' => [
                    'users' => [],
                    'pagination' => [
                        'total' => 0,
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 10,
                    ],
                ],
            ], 500);
        }
    }
}
