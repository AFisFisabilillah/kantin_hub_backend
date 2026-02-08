<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ServiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post("/login", [AuthenticateController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get("/dashboard", [DashboardController::class, 'index']);
    Route::delete("/logout", [AuthenticateController::class, 'logout']);
    Route::post("/profile/update", [AuthenticateController::class, 'update']);
    Route::get("/profile", [AuthenticateController::class, 'profile']);

    Route::post("/products/import", [ProductController::class, 'import']);
    Route::get("/products/export", [ProductController::class, 'export']);
    Route::get('/products/trashed', [ProductController::class, 'trashed']);
    Route::post('/products/{id}/restore', [ProductController::class, 'restore']);
    Route::delete("/products/deletes",[ProductController::class, 'destroyAll']);
    Route::post("/products/restore", [ProductController::class, 'restoreAll']);
    Route::delete("/products/force", [ProductController::class, 'forceAll']);
    Route::delete('/products/{id}/force', [ProductController::class, 'forceDelete']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::post('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);


    Route::get("/admin", [AdminController::class, 'index']);
    Route::post("/admin", [AdminController::class, 'store']);
    Route::get("/admin/{admin}", [AdminController::class, 'show']);
    Route::post("/admin/{admin}", [AdminController::class, 'update']);
    Route::delete("/admin/{admin}", [AdminController::class, 'destroy']);

    Route::get("/sales/trash",[SaleController::class, 'trashed']);
    Route::post("/sales/restore/{id}",[SaleController::class, 'restore']);
    Route::delete("/sales/force/{id}",[SaleController::class, 'force']);
    Route::post('/sales/import', [SaleController::class, 'import']);
    Route::get('/sales/export', [SaleController::class, 'export']);
    Route::delete('/sales/deletes', [SaleController::class, 'destroyAll']);

    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/sales', [SaleController::class, 'index']);
    Route::get('/sales/{sale}', [SaleController::class, 'show']);
    Route::delete("/sales/{sale}", [SaleController::class, 'destroy']);
    Route::patch("/sales/{sale}", [SaleController::class, 'update']);

});
