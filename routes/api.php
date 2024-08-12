<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PropertyController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

//? user APIs routes
Route::post('/register', [AuthController::class, 'registerSubmit']);
Route::post('/login', [AuthController::class, 'loginSubmit']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::post('/forgot-password', [AuthController::class, 'submitForgetPasswordForm']);
Route::post('/reset-password', [AuthController::class, 'submitResetPasswordForm']);
Route::get("/home", [PropertyController::class, 'index']);
Route::get("/property", [PropertyController::class, 'show']);
// Route::get("/property/{address}/{id}", [PropertyController::class, 'details']);
Route::get("/property/{id}", [PropertyController::class, 'details']);
//add properties
Route::post("/add-property", [PropertyController::class, 'store']);
//property-types
Route::get("/property-types", [PropertyController::class, 'propertyTypes']);
//filterProperties
Route::get("/filter-properties", [PropertyController::class, 'filterProperties']);

//? Admin APIs routes
Route::post('/admin/login', [LoginController::class, 'login']);
//show NewProperties
Route::get('/admin/home', [AdminController::class, 'showNewProperties']);
//add New Property
Route::post('/admin/property', [AdminController::class, 'addProperty']);
//show Property details
Route::get('/admin/property/{id}', [AdminController::class, 'showPropertyDetails']);
//change Property status
Route::get('/admin/property/{id}/status', [AdminController::class, 'changeStatus']);
