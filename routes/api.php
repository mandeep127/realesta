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
Route::post('/login', [AuthController::class, 'loginSubmit'])->name('login');
Route::post('/forgot-password', [AuthController::class, 'submitForgetPasswordForm']);
Route::post('/reset-password', [AuthController::class, 'submitResetPasswordForm']);
Route::get("/home", [PropertyController::class, 'index']);
// Route::get("/property", [PropertyController::class, 'show']);
// Route::get("/property/{address}/{id}", [PropertyController::class, 'details']);
Route::get("/property/{id}", [PropertyController::class, 'details']);
//add properties
Route::post("/add-property", [PropertyController::class, 'store']);
//property-types
Route::get("/property-types", [PropertyController::class, 'propertyTypes']);
//filterProperties
Route::get("/filter-properties", [PropertyController::class, 'filterProperties']);


Route::group(['middleware' => ['auth:api']], function () {
     Route::group(['middleware' => ['role:User']], function () {

          Route::post('/change-password', [AuthController::class, 'changePassword']);

          Route::post('/logout', [AuthController::class, 'logoutSubmit']);
          //showProfile
          Route::get('/profile', [AuthController::class, 'showProfile']);
     });

     Route::group(['middleware' => ['role:Admin']], function () {
          //? Admin APIs routes

          Route::post('/admin/logout', [LoginController::class, 'logout']);
          //show NewProperties
          Route::get('/admin/home', [AdminController::class, 'showNewProperties']);
          //show all properties
          Route::get('/admin/properties', [AdminController::class, 'activeProperty']);
          //add New Property
          Route::post('/admin/property', [AdminController::class, 'addProperty']);
          //show Property details
          Route::get('/admin/property/{id}', [AdminController::class, 'showPropertyDetails']);
          //change Property status
          Route::get('/admin/property/{id}/status', [AdminController::class, 'changeStatus']);
          //showUserDetails
          Route::get('/admin/user/{id}', [AdminController::class, 'showUserDetails']);
          //fetchBuyerUsers
          Route::get('/admin/users', [AdminController::class, 'fetchUsers']);
          //fetchSellerUsers
          // Route::get('/admin/users/Seller', [AdminController::class, 'fetchSellerUsers']);
          //adminProfileDetails
          Route::get('/admin/profile', [LoginController::class, 'showAdminProfile']);
     });
});
Route::post('/admin/login', [LoginController::class, 'login']);
