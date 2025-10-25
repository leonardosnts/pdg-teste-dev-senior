<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkSampleIngestionRequest;
use App\Models\MarineLab;
use App\Services\SampleIngestionService;
use Illuminate\Http\JsonResponse;

class BulkSampleIngestionController extends Controller
{
    public function __construct(private SampleIngestionService $service) {}

    public function __invoke(BulkSampleIngestionRequest $request): JsonResponse
    {
        $lab = MarineLab::query()->where('alias', $request->input('lab_alias'))->firstOrFail();
        $response = $this->service->ingest($lab, $request->validated());

        return response()->json($response, 202);
    }
}
