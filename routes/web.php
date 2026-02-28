<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Sadewas Hub API is rooning smoothly.',
        'version' => '1.0.0',
        'timestamp' => now()->toDateString()
    ]);
});
