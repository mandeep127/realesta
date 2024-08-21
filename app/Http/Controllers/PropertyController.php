<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use App\Models\PropertyType;
use App\Models\SubImages;
use App\Models\User;

class PropertyController extends Controller
{
     //HomePage View
     public function index()
     {
          try {
               // $properties = Property::inRandomOrder()
               //      ->limit(3)
               //      ->get();

               $properties = Property::where('status', '1')
                    ->where('created_at', '>', now()->subMonth())
                    ->orderBy('created_at', 'desc')
                    ->limit(4)
                    ->get();


               return response()->json([
                    'message' => 'successfully! Fetching Items',
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

     // Property Details View 
     // public function show(Request $request)
     // {
     //      $keyword = $request->input('keyword', '');
     //      $propertyType = $request->input('property_type', 'all');

     //      // Build the query
     //      $query = Property::query();

     //      // Filter by property type if provided and not 'all'
     //      if ($propertyType && $propertyType !== 'all') {
     //           $query->where('property_type_id', $propertyType);
     //      }

     //      // Add search conditions
     //      if (!empty($keyword)) {
     //           $query->where(function ($q) use ($keyword) {
     //                $q->where('address', 'LIKE', "%{$keyword}%")
     //                     ->orWhere('city', 'LIKE', "%{$keyword}%")
     //                     ->orWhere('state', 'LIKE', "%{$keyword}%")

     //                     ->orWhere('country', 'LIKE', "%{$keyword}%");
     //           });
     //      }

     //      // Execute the query
     //      $properties = $query->get();

     //      // Check if no properties were found
     //      if ($properties->isEmpty()) {
     //           return response()->json([
     //                'msg' => 'Property not found for the given search criteria',
     //                'code' => 404,
     //                'data' => [],
     //           ], 404);
     //      }

     //      return response()->json([
     //           'msg' => 'Properties fetched successfully!',
     //           'code' => 200,
     //           'data' => $properties,
     //      ]);
     // }







     // public function details($address, $id)
     // {
     //      try {
     //           if (!($address) || !($id)) {
     //                return response()->json([
     //                     'msg' => 'url is wrong .......',
     //                     'code' => 400,
     //                     'data' => [],
     //                ], 400);
     //           }

     //           if (!is_numeric($id)) {
     //                return response()->json([
     //                     'msg' => 'The ID parameter must be numeric.',
     //                     'code' => 400,
     //                     'data' => [],
     //                ], 400);
     //           }

     //           // Initialize the query
     //           $query = Property::query();

     //           // Filter by 'address'
     //           $query->where('address', 'LIKE', "%{$address}%");

     //           // Filter by 'id'
     //           $query->where('id', $id);

     //           // Execute the query and get the results
     //           $properties = $query->get();

     //           if ($properties->isEmpty()) {
     //                return response()->json([
     //                     'msg' => 'No properties found',
     //                     'code' => 404,
     //                     'data' => [],
     //                ], 404);
     //           }

     //           return response()->json([
     //                'msg' => 'Properties fetched successfully!',
     //                'code' => 200,
     //                'data' => $properties,
     //           ]);
     //      } catch (QueryException $e) {
     //           return response()->json([
     //                'msg' => 'Database query error',
     //                'code' => 500,
     //                'data' => [],
     //           ], 500);
     //      } catch (\Exception $e) {
     //           return response()->json([
     //                'msg' => 'An unexpected errloginUserApior occurred',
     //                'code' => 500,
     //                'data' => [],
     //           ], 500);
     //      }
     // }
     public function propertyTypes()
     {
          $propertyTypes = PropertyType::all();
          return response()->json([
               'message' => 'Successfully retrieved property types.',
               'code' => 200,
               'data' => $propertyTypes,
          ], 200);
     }


     public function store(Request $request)
     {
          
          try {
               // Validate the incoming request
               $validator = Validator::make($request->all(), [
                    'property_type_id' => 'required|string',
                    'price' => 'required|numeric',
                    'bedrooms' => 'required|integer',
                    'bathrooms' => 'required|integer',
                    'size' => 'required|numeric',
                    'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate image file
                    'sub_images' => 'required|array|min:1',
                    'sub_images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validate multiple sub-images
                    'description' => 'required|string',
                    'address' => 'required|string',
                    'city' => 'required|string',
                    'state' => 'required|string',
                    'pincode' => 'required|string',
                    'country' => 'required|string',
               ]);

               // If validation fails, throw ValidationException
               if ($validator->fails()) {
                    throw new ValidationException($validator);
               }

               // Dummy user ID
               $userId = 1; // Replace with your actual logic to fetch the user ID

               // Handle main image upload
               $mainImagePath = null;
               if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $destinationPath = 'public/images';
                    $image->move(public_path($destinationPath), $filename);
                    $mainImagePath = $destinationPath . '/' . $filename;
               } else {
                    throw new \Exception('Main image file is required.');
               }

               // Create the property record
               $property = Property::create([
                    'property_type_id' => $request->input('property_type_id'),
                    'price' => $request->input('price'),
                    'bedrooms' => $request->input('bedrooms'),
                    'bathrooms' => $request->input('bathrooms'),
                    'size' => $request->input('size'),
                    'image' => $mainImagePath,
                    'description' => $request->input('description'),
                    'address' => $request->input('address'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'pincode' => $request->input('pincode'),
                    'country' => $request->input('country'),
                    'user_id' => $userId,
               ]);

               // Update user type to 3
               User::where('id', $userId)->update(['type' => 3]);

               $subImages = [];
               if ($request->hasFile('sub_images')) {
                    foreach ($request->file('sub_images') as $subImage) {
                         $filename = time() . '_' . $subImage->getClientOriginalName();
                         $destinationPath = 'public/images/sub_images';
                         $subImage->move(public_path($destinationPath), $filename);
                         $path = $destinationPath . '/' . $filename;
                         // Store in the database
                         $subImageRecord = SubImages::create([
                              'property_id' => $property->id,
                              'sub_images' => $path,
                         ]);

                         $subImages[] = $subImageRecord;
                    }
               } else {
                    throw new \Exception('sub-images not selected.');
               }

               // Return success response
               return response()->json([
                    'message' => 'Property created successfully!',
                    'code' => 201,
                    'data' => $property,
                    'sub_images' => $subImages,
               ], 201);
          } catch (ValidationException $e) {
               // Handle validation exceptions
               return response()->json([
                    'message' => 'Validation error',
                    'code' => 422,
                    'errors' => $e->errors(),
                    'data' => [],
               ], 422);
          } catch (\Exception $e) {
               // Handle other exceptions
               return response()->json([
                    'message' => 'An error occurred',
                    'code' => 500,
                    'errors' => $e->getMessage(),
                    'data' => [],
               ], 500);
          }
     }
     //properties details
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
               $property_sub_images = SubImages::where('property_id', $id)->take(5)->get();

               return response()->json([
                    'message' => 'Successfully retrieved property details',
                    'code' => 200,
                    'data' => [
                         'property' => $property,
                         'property_sub_images' => $property_sub_images,
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


     //      public function filterProperties(Request $request)
     // {
     //     try {
     //         $query = Property::query();

     //         // Validate and filter by property type
     //         if ($request->has('property_type_id')) {
     //             $propertyTypeId = $request->input('property_type_id');
     //             if (in_array($propertyTypeId, [1, 2])) { // Only allow 1 (Residential) and 2 (MultiFamily)
     //                 $query->where('property_type_id', $propertyTypeId);
     //             } else {
     //                 return response()->json(['error' => 'Invalid property type.'], 400);
     //             }
     //         }

     //         // Filter by price range
     //         $minPrice = $request->input('min_price');
     //         $maxPrice = $request->input('max_price');

     //         if (!empty($minPrice) && is_numeric($minPrice)) {
     //             $query->where('price', '>=', $minPrice);
     //         }
     //         if (!empty($maxPrice) && is_numeric($maxPrice)) {
     //             $query->where('price', '<=', $maxPrice);
     //         }

     //         // Filter by bedroom range
     //         $minBedrooms = $request->input('min_bedrooms');
     //         $maxBedrooms = $request->input('max_bedrooms');

     //         if (!empty($minBedrooms) && is_numeric($minBedrooms)) {
     //             $query->where('bedrooms', '>=', $minBedrooms);
     //         }
     //         if (!empty($maxBedrooms) && is_numeric($maxBedrooms)) {
     //             $query->where('bedrooms', '<=', $maxBedrooms);
     //         }

     //         // Execute the query and get the results
     //         $properties = $query->get();

     //         // Check if properties are found
     //         if ($properties->isEmpty()) {
     //             return response()->json(['message' => 'No properties found matching the criteria.'], 404);
     //         }

     //         return response()->json([
     //             'success' => true,
     //             'message' => 'Properties successfully fetched.',
     //             'data' => $properties
     //         ], 200);
     //     } catch (\Exception $e) {
     //         // Handle any exceptions that occur
     //         return response()->json([
     //             'success' => false,
     //             'error' => 'An error occurred while filtering properties: ' . $e->getMessage()
     //         ], 500);
     //     }
     // }

     public function filterProperties(Request $request)
     {
          try {
               // Retrieve parameters from the request
               $keyword = $request->input('keyword', '');
               $propertyTypeId = $request->input('property_type_id', '');

               // Start the query
               $query = Property::query();

               // Filter by property type if provided and not empty
               if (!empty($propertyTypeId)) {
                    $query->where('property_type_id', $propertyTypeId);
               }

               // Add search conditions based on keyword
               if (!empty($keyword)) {
                    $query->where(function ($q) use ($keyword) {
                         $q->where('address', 'LIKE', "%{$keyword}%")
                              ->orWhere('city', 'LIKE', "%{$keyword}%")
                              ->orWhere('state', 'LIKE', "%{$keyword}%")
                              ->orWhere('pincode', 'LIKE', "%{$keyword}%")
                              ->orWhere('country', 'LIKE', "%{$keyword}%");
                    });
               }

               // Optionally, log the query for debugging
               // \Log::info('Property Query: ' . $query->toSql(), $query->getBindings());

               // Execute the query
               $properties = $query->get();

               // Check if properties were found
               if ($properties->isEmpty()) {
                    return response()->json([
                         'msg' => 'Property not found for the given search criteria',
                         'code' => 404,
                         'data' => [],
                    ], 404);
               }

               // Return the properties in the response
               return response()->json([
                    'msg' => 'Properties fetched successfully!',
                    'code' => 200,
                    'data' => $properties,
               ]);
          } catch (\Exception $e) {
               // Log the exception and return an error response
               \Log::error('Error fetching properties: ' . $e->getMessage());
               return response()->json([
                    'msg' => 'An unexpected error occurred',
                    'code' => 500,
                    'data' => [],
               ], 500);
          }
     }
}
