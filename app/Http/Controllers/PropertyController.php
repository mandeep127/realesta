<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
     //HomePage View
     public function index()
     {
          try {
               $properties = Property::inRandomOrder()
                    ->limit(3)
                    ->get();

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

     public function details($address, $id)
     {
          try {
               if (!($address) || !($id)) {
                    return response()->json([
                         'msg' => 'url is wrong .......',
                         'code' => 400,
                         'data' => [],
                    ], 400);
               }

               if (!is_numeric($id)) {
                    return response()->json([
                         'msg' => 'The ID parameter must be numeric.',
                         'code' => 400,
                         'data' => [],
                    ], 400);
               }

               // Initialize the query
               $query = Property::query();

               // Filter by 'address'
               $query->where('address', 'LIKE', "%{$address}%");

               // Filter by 'id'
               $query->where('id', $id);

               // Execute the query and get the results
               $properties = $query->get();

               if ($properties->isEmpty()) {
                    return response()->json([
                         'msg' => 'No properties found',
                         'code' => 404,
                         'data' => [],
                    ], 404);
               }

               return response()->json([
                    'msg' => 'Properties fetched successfully!',
                    'code' => 200,
                    'data' => $properties,
               ]);
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
}
