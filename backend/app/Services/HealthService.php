<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

class HealthService
{
    /**
     * Check application and database health status.
     *
     * @return array<string, mixed>
     */
    public function check(): array
    {
        $dbStatus = 'disconnected';

        try {
            DB::connection()->getPdo();
            $dbStatus = 'connected';
        } catch (Throwable) {
            $dbStatus = 'disconnected';
        }

        return [
            'status' => $dbStatus === 'connected' ? 'ok' : 'degraded',
            'timestamp' => now()->toISOString(),
            'database' => $dbStatus,
            'environment' => config('app.env'),
        ];
    }
}
