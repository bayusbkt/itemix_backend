<?php

use App\Http\Controllers\Auth\AuthenticationController;
use App\Http\Controllers\Category\CategoryController;
use App\Http\Controllers\Item\ItemController;
use App\Http\Controllers\Location\LocationController;
use App\Http\Controllers\Transaction\TransactionController;
use App\Http\Middleware\IsAdmin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//Default Route for handling error unauthorized
Route::get('/', function () {
    return response()->json([
        'status' => false,
        'message' => "Access Denied"
    ], 401);
})->name('login');


//Authentication Routes
Route::post('/auth/register', [AuthenticationController::class, 'register']);
Route::post('/auth/login', [AuthenticationController::class, 'login']);

//Main Routes
Route::middleware(['auth:sanctum', IsAdmin::class])->group(function () {

    //User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/change-password', [AuthenticationController::class, "changePassword"]);

    //Category
    Route::get('/category', [CategoryController::class, 'index']);
    Route::get('/category/{id}', [CategoryController::class, 'show']);
    Route::post('/category', [CategoryController::class, 'store']);
    Route::put('/category/{id}', [CategoryController::class, 'update']);
    Route::delete('/category/{id}', [CategoryController::class, 'destroy']);

    //Location
    Route::get('/location', [LocationController::class, 'index']);
    Route::get('/location/{id}', [LocationController::class, 'show']);
    Route::post('/location', [LocationController::class, 'store']);
    Route::put('/location/{id}', [LocationController::class, 'update']);
    Route::delete('/location/{id}', [LocationController::class, 'destroy']);

    //Item
    Route::get('/item', [ItemController::class, 'index']);
    Route::get('/item/{id}', [ItemController::class, 'show']);
    Route::post('/item', [ItemController::class, 'store']);
    Route::put('/item/{id}', [ItemController::class, 'update']);
    Route::delete('/item/{id}', [ItemController::class, 'destroy']);

    //Transaction
    Route::prefix('transaction')->group(function(){
        Route::get('/', [TransactionController::class, 'index']);
        Route::post('/borrow', [TransactionController::class, 'borrow']);
        Route::post('/return', [TransactionController::class, 'return']);
        Route::post('/add-stock', [TransactionController::class, 'addStock']);
        Route::post('/remove-stock', [TransactionController::class, 'removeStock']);
    });
});
