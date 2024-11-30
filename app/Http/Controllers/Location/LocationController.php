<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    public function index()
    {
        try {
            $location = Location::all();
            return response()->json([
                'status' => true,
                'message' => 'Success Get All Locations',
                'data' => $location
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $e->getMessage()
                ]
            );
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:locations,name',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 422);
            }

            $location = Location::create($validator->validated());

            return response()->json([
                'status' => true,
                'message' => 'Success Create Location',
                'data' => $location
            ], 201);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $e->getMessage()
                ]
            );
        }
    }

    public function show(string $id)
    {
        try {
            $location = Location::find($id);
            if (!$location) {
                return response()->json([
                    'status' => false,
                    'message' => "Location not found"
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Success Get Location',
                'data' => $location
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $e->getMessage()
                ]
            );
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255|unique:locations,name, {$id}',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => $validator->errors()
                    ],
                    422
                );
            }

            $location = Location::find($id);
            if (!$location) {
                return response()->json([
                    'status' => false,
                    'message' => "Location not found"
                ], 404);
            }

            $location->fill($request->only(['name', 'description']))->save();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'Success Update Location',
                    'data' => $location
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $e->getMessage()
                ]
            );
        }
    }

    public function destroy(string $id)
    {
        try {
            $location = Location::find($id);
            if (!$location) {
                return response()->json([
                    'status' => false,
                    'message' => 'Location not found'
                ], 404);
            }

            $location->delete();

            return response()->json([
                'status' => true,
                'message' => 'Success Delete Location'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'status' => false,
                    'message' => $e->getMessage()
                ]
            );
        }
    }
}
