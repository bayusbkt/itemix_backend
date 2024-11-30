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

    public function index()
    {
        try {
            $transactions = Transaction::with(['item', 'user'])->orderBy('created_at', 'desc')->paginate(15);
            return response()->json([
                'status' => true,
                'message' => 'Success get all transactions',
                'data' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to get all transactions',
                'error' => $e->getMessage()
            ], 500);
        }
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
                'message' => 'Success borrow item',
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

    public function return(Request $request)
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

            $borrowTransaction = Transaction::where('item_id', $request->item_id)
                ->where('user_id', Auth::id())
                ->where('type', 'Borrow')
                ->where('quantity', '>=', $request->quantity)
                ->first();

            if (!$borrowTransaction) {
                return response()->json([
                    'status' => false,
                    'message' => 'No matching borrow transaction found'
                ], 400);
            }

            $transaction = Transaction::create([
                'item_id' => $request->item_id,
                'user_id' => Auth::id(),
                'quantity' => $request->quantity,
                'type' => 'Return',
                'date' => $request->date
            ]);

            $item->stock += $request->quantity;
            $item->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Success return item',
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to return item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function addStock(Request $request)
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

            $transaction = Transaction::create([
                'item_id' => $request->item_id,
                'user_id' => Auth::id(),
                'quantity' => $request->quantity,
                'type' => 'Add',
                'date' => $request->date
            ]);

            $item->stock += $request->quantity;
            $item->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Success add stock',
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to add stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function removeStock(Request $request)
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
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient stock for removal'
                ], 400);
            }

            $transaction = Transaction::create([
                'item_id' => $request->item_id,
                'user_id' => Auth::id(),
                'quantity' => $request->quantity,
                'type' => 'Remove',
                'date' => $request->date
            ]);

            $item->stock -= $request->quantity;
            $item->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Success remove stock',
                'data' => $transaction
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to add stock',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
