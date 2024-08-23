<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    //? Admin home API

    public function showNewProperties()
    {
        try {
            // Fetch new properties
            $properties = Property::where('status', '1')
                ->where('created_at', '>', now()->subMonth())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Fetch new users of type 2
            $users = User::where('type', '<>', '1') // Exclude users with type '1'
                ->where('created_at', '>', now()->subMonth()) // Filter by creation date
                ->orderBy('created_at', 'desc') // Order by creation date
                ->limit(10) // Limit the results to 10
                ->get();

            $totalPropertiesCount = Property::count();
            $totalUsersCount = User::where('type', '<>', '1')
                ->count();

            return response()->json([
                'msg' => 'New properties and users fetched successfully!',
                'code' => 200,
                'data' => [
                    'properties' => $properties,
                    'users' => $users,
                    'properties_count' => $totalPropertiesCount,
                    'users_count' => $totalUsersCount,
                ],
            ]);
        } catch (\Exception $e) {
            // Log the exception message
            \Log::error($e->getMessage());

            return response()->json([
                'msg' => 'An unexpected error occurred',
                'code' => 500,
                'data' => [],
            ], 500);
        }
    }




    // property section view active property
    public function activeProperty()
    {
        try {
            $itemsPerPage = 10;
            $properties = Property::paginate($itemsPerPage);

            return response()->json([
                'message' => 'Successfully! Fetching Items',
                'code' => 200,
                'data' => $properties,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching the items.',
                'code' => 500,
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }



    // Add a new property for buy or sell
    public function addProperty(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:255',
            'price' => 'required|numeric',
            'bedrooms' => 'required|integer',
            'bathrooms' => 'required|integer',
            'size' => 'required|numeric',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'type' => 'required|string|max:50', // Type can be 'buy' or 'sell'
            'status' => 'required|string|max:50', // Status could be 'available', 'sold', etc.
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg' => 'Validation failed',
                'code' => 400,
                'data' => $validator->errors(),
            ], 400);
        }

        try {
            // Create a new property
            $property = Property::create($request->all());

            return response()->json([
                'msg' => 'Property added successfully!',
                'code' => 201,
                'data' => $property,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'An unexpected error occurred',
                'code' => 500,
                'data' => [],
            ], 500);
        }
    }

    // public function details($id)
    // {
    //     try {
    //         $property = Property::find($id);

    //         if (!$property) {
    //             return response()->json([
    //                 'error' => 'Property not found',
    //                 'code' => 404,
    //                 'data' => [],
    //             ], 404);
    //         }

    //         // Fetch related images 
    //         // $property_sub_images = PropertySubImages::where('property_id', $property_id)->take(5)->get();


    //         return response()->json([
    //             'message' => 'Successfully retrieved property details',
    //             'code' => 200,
    //             'data' => [
    //                 'property' => $property,
    //                 // 'property_sub_images' => $property_sub_images,

    //             ]
    //         ], 200);
    //     } catch (\Exception $e) {
    //         // Handle exceptions and return error response
    //         return response()->json([
    //             'error' => 'An error occurred while processing your request',
    //             'message' => $e->getMessage(),
    //             'code' => 500,
    //             'data' => [],
    //         ], 500);
    //     }
    // }

    // Show full details of a specific property
    public function showPropertyDetails($id)
    {
        try {
            // Find the property by ID
            $property = Property::findOrFail($id);

            // Retrieve user_id from the property
            $userId = $property->user_id;

            // Fetch user details using user_id
            $user = User::find($userId);

            // Check if user exists
            if (!$user) {
                return response()->json([
                    'msg' => 'User associated with the property not found',
                    'code' => 404,
                    'data' => [
                        'property' => $property,
                        'user' => null,
                    ],
                ], 404);
            }

            // Return property and user details
            return response()->json([
                'msg' => 'Property details fetched successfully!',
                'code' => 200,
                'data' => [
                    'property' => $property,
                    'user' => $user,
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'msg' => 'Property not found',
                'code' => 404,
                'data' => [
                    'property' => null,
                    'user' => null,
                ],
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'An unexpected error occurred',
                'code' => 500,
                'data' => [
                    'property' => null,
                    'user' => null,
                ],
            ], 500);
        }
    }


    // Change the status of an existing property
    public function changeStatus(Request $request, $id)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'msg' => 'Validation failed',
                'code' => 400,
                'data' => $validator->errors(),
            ], 400);
        }

        try {
            // Find the property by ID
            $property = Property::findOrFail($id);

            // Update the status
            $property->status = $request->input('status');
            $property->save();

            return response()->json([
                'msg' => 'Property status updated successfully!',
                'code' => 200,
                'data' => $property,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'Property not found',
                'code' => 404,
                'data' => [],
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'An unexpected error occurred',
                'code' => 500,
                'data' => [],
            ], 500);
        }
    }

    public function fetchUsers(Request $request)
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


    // public function fetchSellerUsers(Request $request)
    // {
    //     try {
    //         // Define the number of users per page
    //         $perPage = 10;

    //         // Get the current page from the request, defaulting to 1
    //         $currentPage = $request->input('page', 1);

    //         // Fetch users of type 3 with pagination
    //         $users = User::where('type', 3)
    //             ->paginate($perPage, ['*'], 'page', $currentPage);

    //         // Return paginated user details
    //         return response()->json([
    //             'msg' => 'Users fetched successfully!',
    //             'code' => 200,
    //             'data' => [
    //                 'users' => $users->items(),
    //                 'pagination' => [
    //                     'total' => $users->total(),
    //                     'current_page' => $users->currentPage(),
    //                     'last_page' => $users->lastPage(),
    //                     'per_page' => $users->perPage(),
    //                 ],
    //             ],
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'msg' => 'An unexpected error occurred',
    //             'code' => 500,
    //             'data' => [
    //                 'users' => [],
    //                 'pagination' => [
    //                     'total' => 0,
    //                     'current_page' => 1,
    //                     'last_page' => 1,
    //                     'per_page' => 10,
    //                 ],
    //             ],
    //         ], 500);
    //     }
    // }

    public function showUserDetails($id, Request $request)
    {
        // Retrieve the current page from the request, defaulting to 1 if not provided
        $currentPage = $request->input('page', 1);
        $perPage = 10; // Number of properties per page

        try {
            // Find the user by ID
            $user = User::findOrFail($id);

            // Fetch paginated properties associated with the user
            $properties = Property::where('user_id', $id)
                ->limit(5)
                ->get();

            return response()->json([
                'msg' => 'User and paginated properties fetched successfully!',
                'code' => 200,
                'data' => [
                    'user' => $user,
                    'properties' => $properties,
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'msg' => 'User not found',
                'code' => 404,
                'data' => [
                    'user' => null,
                    'properties' => [],
                ],
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'An unexpected error occurred',
                'code' => 500,
                'data' => [
                    'user' => null,
                    'properties' => [],
                ],
            ], 500);
        }
    }
}
