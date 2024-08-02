<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Exception;

class AuthController extends Controller
{
     public function registerSubmit(Request $request)
     {
          try {
               $validator = Validator::make($request->all(), [
                    'email' => 'required|string|email|unique:users',
                    'password' => 'required|string|min:6',
                    'confirm_password' => 'required_with:password|same:password',
               ]);

               if ($validator->fails()) {
                    return response()->json([
                         'message' => 'Validation error',
                         'code' => 422,
                         'errors' => $validator->errors(),
                         'data' => [],
                    ], 422);
               }

               $password = Hash::make($request->password);
               $user = User::create([
                    'email' => $request->email,
                    'password' => $password,
               ]);
               $role = Role::findByName('User', 'Api');
               $user->assignRole($role);

               return response()->json([
                    'message' => 'Registration successful!',
                    'code' => 201,
                    'data' => $user,
               ], 201);
          } catch (Exception $e) {
               return response()->json([
                    'message' => 'An error occurred',
                    'code' => 500,
                    'errors' => $e->getMessage(),
                    'data' => [],
               ], 500);
          }
     }


     //?  login API

     public function loginSubmit(Request $request)
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
          } catch (Exception $e) {
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
                         'error' => 'User not found.',
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

     //? Change password API

     public function changePassword(Request $request)
     {
          try {
               $validator = Validator::make($request->all(), [
                    'current_password' => 'required|string',
                    'new_password' => 'required|string|min:6',
                    'confirm_new_password' => 'required_with:new_password|same:new_password',
               ]);

               if ($validator->fails()) {
                    return response()->json([
                         'message' => 'Validation error',
                         'code' => 422,
                         'errors' => $validator->errors(),
                         'data' => [],
                    ], 422);
               }

               $user = Auth::user();

               if (!$user) {
                    return response()->json([
                         'error' => 'User not authenticated.',
                         'code' => 401
                    ], 401);
               }

               if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                         'error' => 'Current password does not match.',
                         'code' => 401
                    ], 401);
               }

               if ($request->current_password === $request->new_password) {
                    return response()->json([
                         'error' => 'New password cannot be the same as current password.',
                         'code' => 400
                    ], 400);
               }

               $user->password = Hash::make($request->new_password);
               $user->save();

               return response()->json([
                    'message' => 'Password updated successfully.',
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
