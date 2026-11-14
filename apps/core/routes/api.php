<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EKAA Core API Routes (§17.3, §17.5)
|--------------------------------------------------------------------------
*/

// Public Health
Route::get('/health', [HealthController::class, 'check']);

// Internal Routes (Accessible only inside cluster network)
Route::prefix('internal/v1')->group(function () {
    Route::post('/connector/items', function () {
        return response()->json(['status' => 'received']);
    });
    Route::post('/ingestions/{id}/status', function ($id) {
        return response()->json(['status' => 'acknowledged', 'ingestion_id' => $id]);
    });
});
