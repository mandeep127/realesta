<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
                    'error' => 'Admin not found.',
                    'code' => 404
                ], 404);
            }
            $user->token()->revoke();
            Auth::logout();

            return response()->json([
                'message' => 'Logged out successfully.',
                'code' => 200
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Internal Server Error!',
                'code' => 500
            ], 500);
        }
    }

    //profile
    public function showAdminProfile(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'error' => 'Admin not found.',
                    'code' => 404,
                ], 404);
            }

            return response()->json([
                'message' => 'Admin profile fetched successfully.',
                'code' => 200,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->toDateTimeString(),
                    'updated_at' => $user->updated_at->toDateTimeString(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the Admin profile.',
                'code' => 500,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
