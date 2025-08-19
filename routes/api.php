<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
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

//------------------------------ Public Routes ------------------------------

Route::prefix('auth')->group(function(){
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

//------------------------------ Private Routes ------------------------------

Route::middleware('auth:sanctum')->group(function(){
    //------------------------------ Task routes ------------------------------
    Route::prefix('tasks')->group(function(){
        Route::post('/', [TaskController::class, 'store']);
    });
});
