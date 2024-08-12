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
            $properties = Property::inRandomOrder()
                ->limit(3)
                ->get();

            // Fetch new users of type 2
            $users = User::where('type', 2)
                ->where('created_at', '>', now()->subMonth())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'msg' => 'New properties and users fetched successfully!',
                'code' => 200,
                'data' => [
                    'properties' => $properties,
                    'users' => $users,
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

    public function details($id)
    {
        try {
            $property = Property::find($id);

            if (!$property) {
                return response()->json([
                    'error' => 'Property not found',
                    'code' => 404,
                    'data' => [],
                ], 404);
            }

            // Fetch related images 
            // $property_sub_images = PropertySubImages::where('property_id', $property_id)->take(5)->get();


            return response()->json([
                'message' => 'Successfully retrieved property details',
                'code' => 200,
                'data' => [
                    'property' => $property,
                    // 'property_sub_images' => $property_sub_images,

                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle exceptions and return error response
            return response()->json([
                'error' => 'An error occurred while processing your request',
                'message' => $e->getMessage(),
                'code' => 500,
                'data' => [],
            ], 500);
        }
    }

    // Show full details of a specific property
    public function showPropertyDetails($id)
    {
        try {
            // Find the property by ID
            $property = Property::find($id);

            return response()->json([
                'msg' => 'Property details fetched successfully!',
                'code' => 200,
                'data' => $property,
            ]);
        } catch (ModelNotFoundException $e) {
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
}
