<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function borrow(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $item = Item::findOrFail($request->item_id);
            if ($item->stock < $request->quantity) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient stock for borrowing'
                ], 400);
            }

            $transaction = Transaction::create([
                'item_id' => $request->item_id,
                'user_id' => Auth::id(),
                'quantity' => $request->quantity,
                'type' => 'Borrow',
                'date' => $request->date
            ]);

            $item->stock -= $request->quantity;
            $item->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Success Borrow Item',
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to borrow item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
