<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
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

    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/trashed', [ServiceController::class, 'trashed']);
    Route::post('/services/{id}/restore', [ServiceController::class, 'restore']);
    Route::delete('/services/{id}/force', [ServiceController::class, 'forceDelete']);

    Route::get('/services/export', [ServiceController::class, 'export']);
    Route::post("/services", [ServiceController::class, 'store']);
    Route::get("/services/{service}", [ServiceController::class, 'detail']);
    Route::post("/services/{service}", [ServiceController::class, 'update']);
    Route::delete("/services/{service}", [ServiceController::class, 'destroy']);

    Route::get("/admin", [AdminController::class, 'index']);
    Route::post("/admin", [AdminController::class, 'store']);
    Route::get("/admin/{admin}", [AdminController::class, 'show']);
    Route::post("/admin/{admin}", [AdminController::class, 'update']);
    Route::delete("/admin/{admin}", [AdminController::class, 'destroy']);
});
