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
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'code' => 422,
                    'errors' => $validator->errors(),
                    'data' => [],
                ], 422);
            }

            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid credentials',
                    'code' => 401,
                    'data' => [],
                ], 401);
            }

            // Get the authenticated user
            $user = Auth::user();
            $token = $user->createToken('Personal Access Token')->accessToken;    // Generate a token

            return response()->json([
                'message' => 'Login successful!',
                'code' => 200,
                'data' => [
                    'user' => $user,
                    'token' => $token,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred',
                'code' => 500,
                'errors' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
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
}
