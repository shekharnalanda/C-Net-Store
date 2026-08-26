<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/dashboard', fn () => response()->json(['status' => 'ready']));

