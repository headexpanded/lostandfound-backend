<?php

use App\Http\Controllers\LostItemController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes - Fortify handles these automatically
// POST /register - User registration
// POST /login - User login

// Public routes for browsing lost items
Route::get('/lost-items', [LostItemController::class, 'index']);
Route::get('/lost-items/search', [LostItemController::class, 'search']);
Route::get('/lost-items/{lost_item}', [LostItemController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User profile - Fortify handles this automatically
    // GET /user/profile - Get user profile

    // Lost Items (create, update, delete)
    Route::post('/lost-items', [LostItemController::class, 'store']);
    Route::put('/lost-items/{lost_item}', [LostItemController::class, 'update']);
    Route::delete('/lost-items/{lost_item}', [LostItemController::class, 'destroy']);

    // User's own lost items
    Route::get('/my-lost-items', [LostItemController::class, 'myItems']);

    // Messages
    Route::apiResource('messages', MessageController::class);

    // User's messages (inbox)
    Route::get('/my-messages', [MessageController::class, 'myMessages']);

    // Mark message as read
    Route::patch('/messages/{message}/read', [MessageController::class, 'markAsRead']);

    // Logout - Fortify handles this automatically
    // POST /logout - User logout
});
