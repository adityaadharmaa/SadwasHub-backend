<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   return response()->json([
        'status' => 'success',
        'message' => 'Sadewas Hub API is fully operational 🚀',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
        'health' => [
            'database' => DB::connection()->getPdo() ? 'Connected 🟢' : 'Disconnected 🔴',
            'environment' => app()->environment(),
            'php_version' => phpversion(),
        ]
    ]);
});
