<?php

use App\Http\Controllers\AuthenticateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post("/login", [AuthenticateController::class, 'login']);
Route::middleware(['auth:sanctum'])->group(function () {
   Route::delete("/logout", [AuthenticateController::class, 'logout']);
   Route::post("/profile/update", [AuthenticateController::class, 'update']);
   Route::get("/profile", [AuthenticateController::class, 'profile']);
});
