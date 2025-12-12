<?php

use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post("/login", [AuthenticateController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::delete("/logout", [AuthenticateController::class, 'logout']);
    Route::post("/profile/update", [AuthenticateController::class, 'update']);
    Route::get("/profile", [AuthenticateController::class, 'profile']);


    Route::get('/products/trashed', [ProductController::class, 'trashed']);
    Route::post('/products/{id}/restore', [ProductController::class, 'restore']);
    Route::delete('/products/{id}/force', [ProductController::class, 'forceDelete']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::post('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

});
