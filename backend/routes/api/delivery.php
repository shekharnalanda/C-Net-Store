<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Delivery\AssignmentController;

Route::middleware(['auth:sanctum', 'role:delivery_partner'])->group(function (): void {
    Route::get('/assignments', [AssignmentController::class, 'index']);
    Route::patch('/assignments/{assignment}', [AssignmentController::class, 'update']);
    Route::post('/assignments/{assignment}/location', [AssignmentController::class, 'location'])->middleware('throttle:120,1');
});
