<?php

namespace App\Http\Controllers;

use App\Http\Resources\BulkIngestionResource;
use App\Services\SampleIngestionService;
use Illuminate\Http\JsonResponse;

class SampleIngestionStatusController extends Controller
{
    public function __construct(private SampleIngestionService $service) {}

    public function __invoke(string $uuid): JsonResponse
    {
        $ingestionBatch = $this->service->getBatchStatus($uuid);

        if (!$ingestionBatch) {
            return response()->json(['message' => 'Ingestion batch not found'], 404);
        }

        return response()->json(BulkIngestionResource::make($ingestionBatch));
    }
}
