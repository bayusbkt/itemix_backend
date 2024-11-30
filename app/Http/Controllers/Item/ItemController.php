<?php

namespace App\Http\Controllers\Item;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    public function index()
    {
        try {
            $items = Item::with(['category', 'location'])->get();
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Success Get All Items',
                    'data' => $items
                ]
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

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:items,name',
                'category_id' => 'required|exists:categories,id',
                'location_id' => 'required|exists:locations,id',
                'stock' => 'required|integer|min:0',
                'price' => 'required|integer|min:0',
                'description' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 422);
            }

            $item = Item::create($validator->validated());

            return response()->json([
                'status' => true,
                'message' => 'Success Create Item',
                'data' => $item->load(['category', 'location'])
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
            $item = Item::with(['category', 'location'])->find($id);
            if (!$item) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => "Item not found"
                    ]
                );
            }

            return response()->json([
                'status' => true,
                'message' => 'Success Get Item',
                'data' => $item
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
                'name' => 'string|max:255|unique:items,name, {$id}',
                'category_id' => 'exists:categories,id',
                'location_id' => 'exists:locations:id',
                'stock' => 'integer|min:0',
                'price' => 'integer|min:0',
                'description' => 'string|nullable'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => $validator->errors()
                ], 422);
            }

            $item = Item::find($id);
            if (!$item) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => "Item not found"
                    ]
                );
            }

            $item->update($request->only([
                'name',
                'category_id',
                'location_id',
                'stock',
                'price',
                'description'
            ]));

            return response()->json([
                'status' => true,
                'message' => 'Success Update Item',
                'data' => $item
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

    public function destroy(string $id)
    {
        try {
            $item = Item::find($id);
            if (!$item) {
                return response()->json(
                    [
                        'status' => false,
                        'message' => "Item not found"
                    ]
                );
            }

            $item->delete();

            return response()->json([
                'status' => true,
                'message' => 'Success Delete Item',
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
