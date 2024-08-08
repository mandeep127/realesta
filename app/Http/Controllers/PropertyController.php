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

class PropertyController extends Controller
{
     //HomePage View
     public function index()
     {
          try {
               // $properties = Property::inRandomOrder()
               //      ->limit(3)
               //      ->get();

               $properties = Property::where('created_at', '>', Carbon::now()->subMonth()->startOfMonth())
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
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
     public function show(Request $request)
     {
          try {
               // Check if 'keyword' is provided
               $keyword = $request->input('keyword');
               if (!($keyword)) {
                    return response()->json([
                         'msg' => 'The keyword parameter is required.',
                         'code' => 400,
                         'data' => [],
                    ], 400);
               }

               // Retrieve 'keyword' from the request
               $keyword = $request->input('keyword');

               // Initialize the query
               $query = Property::query();

               // Filter by property type if provided
               if ($request->has('property_type')) {
                    $query->where('property_type_id', $request->input('property_type'));
               }

               // Add multiple 'where' clauses to search across different columns
               $query->where(function ($q) use ($keyword) {
                    $q->where('address', 'LIKE', "%{$keyword}%")
                         ->orWhere('city', 'LIKE', "%{$keyword}%")
                         ->orWhere('state', 'LIKE', "%{$keyword}%")
                         ->orWhere('zip', 'LIKE', "%{$keyword}%")
                         ->orWhere('country', 'LIKE', "%{$keyword}%")
                         ->orWhere('price', 'LIKE', "%{$keyword}%")
                         ->orWhere('bedrooms', 'LIKE', "%{$keyword}%")
                         ->orWhere('bathrooms', 'LIKE', "%{$keyword}%")
                         ->orWhere('size', 'LIKE', "%{$keyword}%")
                         ->orWhere('type', 'LIKE', "%{$keyword}%");
               });

               $properties = $query->get();

               return response()->json([
                    'msg' => 'Properties fetched successfully!',
                    'code' => 200,
                    'data' => $properties,
               ]);
          } catch (ModelNotFoundException $e) {
               return response()->json([
                    'msg' => 'Properties not found',
                    'code' => 404,
                    'data' => [],
               ], 404);
          } catch (QueryException $e) {
               return response()->json([
                    'msg' => 'Database query error',
                    'code' => 500,
                    'data' => [],
               ], 500);
          } catch (\Exception $e) {
               return response()->json([
                    'msg' => 'An unexpected error occurred',
                    'code' => 500,
                    'data' => [],
               ], 500);
          }
     }

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


     // Create a new property
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

               // Handle image upload
               $imagePath = null;
               if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $filename = time() . '_' . $image->getClientOriginalName();
                    $destinationPath = 'public/images';
                    $image->move(public_path($destinationPath), $filename);
                    $imagePath = $destinationPath . '/' . $filename;
               } else {
                    throw new \Exception('Image file is required.');
               }

               // Create the property record
               $property = Property::create([
                    'property_type_id' => $request->input('property_type_id'),
                    'price' => $request->input('price'),
                    'bedrooms' => $request->input('bedrooms'),
                    'bathrooms' => $request->input('bathrooms'),
                    'size' => $request->input('size'),
                    'image' => $imagePath,
                    'description' => $request->input('description'),
                    'address' => $request->input('address'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'pincode' => $request->input('pincode'),
                    'country' => $request->input('country'),
                    'user_id' => $userId, // Assign the user ID here
               ]);

               // Return success response
               return response()->json([
                    'message' => 'Property created successfully!',
                    'code' => 201,
                    'data' => $property,
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

     public function filterProperties(Request $request)
     {
          try {
               $query = Property::query();

               // Filter by property type, if provided and valid
               $propertyTypeIds = $request->input('property_type_id');
               if (!empty($propertyTypeIds)) {
                    // Convert comma-separated IDs into an array
                    $propertyTypeIds = explode(',', $propertyTypeIds);

                    // Ensure IDs are integers
                    $propertyTypeIds = array_map('intval', $propertyTypeIds);

                    // Only allow 1 (Residential) and 2 (MultiFamily)
                    if (array_intersect($propertyTypeIds, [1, 2])) {
                         $query->whereIn('property_type_id', $propertyTypeIds);
                    } else {
                         return response()->json(['error' => 'Invalid property type.'], 400);
                    }
               }

               // Filter by price range
               $minPrice = $request->input('min_price');
               $maxPrice = $request->input('max_price');

               if (!empty($minPrice) && is_numeric($minPrice)) {
                    $query->where('price', '>=', $minPrice);
               }
               if (!empty($maxPrice) && is_numeric($maxPrice)) {
                    $query->where('price', '<=', $maxPrice);
               }

               // Filter by bedroom range
               $minBedrooms = $request->input('min_bedrooms');
               $maxBedrooms = $request->input('max_bedrooms');

               if (!empty($minBedrooms) && is_numeric($minBedrooms)) {
                    $query->where('bedrooms', '>=', $minBedrooms);
               }
               if (!empty($maxBedrooms) && is_numeric($maxBedrooms)) {
                    $query->where('bedrooms', '<=', $maxBedrooms);
               }

               // Execute the query and get the results
               $properties = $query->get();

               // Check if properties are found
               if ($properties->isEmpty()) {
                    return response()->json(['message' => 'No properties found matching the criteria.'], 404);
               }

               return response()->json([
                    'success' => true,
                    'message' => 'Properties successfully fetched.',
                    'data' => $properties
               ], 200);
          } catch (\Exception $e) {
               // Handle any exceptions that occur
               return response()->json([
                    'success' => false,
                    'error' => 'An error occurred while filtering properties: ' . $e->getMessage()
               ], 500);
          }
     }
}
