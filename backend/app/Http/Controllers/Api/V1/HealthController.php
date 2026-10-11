<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\HealthResource;
use App\Services\HealthService;

class HealthController extends Controller
{
    public function __construct(
        protected HealthService $healthService
    ) {}

    /**
     * Return application health status.
     */
    public function __invoke(): HealthResource
    {
        $health = $this->healthService->check();

        return new HealthResource($health);
    }
}
