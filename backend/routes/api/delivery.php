<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/assignments', fn () => response()->json(['data' => []]));

