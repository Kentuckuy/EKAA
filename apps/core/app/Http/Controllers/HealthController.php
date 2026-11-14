<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController
{
    /**
     * Public Healthcheck endpoint (§17.3, AC-M0-01).
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'ekaa-core',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
