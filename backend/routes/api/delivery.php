<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Delivery\AssignmentController;
use App\Http\Controllers\Api\Delivery\ProfileController;

Route::middleware(['auth:sanctum', 'role:delivery_partner'])->group(function (): void {
    Route::get('/assignments', [AssignmentController::class, 'index']);
    Route::patch('/assignments/{assignment}', [AssignmentController::class, 'update']);
    Route::post('/assignments/{assignment}/location', [AssignmentController::class, 'location'])->middleware('throttle:120,1');
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/availability', [ProfileController::class, 'availability'])->middleware('throttle:30,1');
    Route::get('/earnings', [ProfileController::class, 'earnings']);
});
