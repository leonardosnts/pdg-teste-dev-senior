<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportSummaryRequest;
use App\Http\Resources\ExpeditionReportResource;
use App\Services\ExpeditionReportService;
use Illuminate\Http\JsonResponse;

class ExpeditionSummaryController extends Controller
{
    public function __construct(private ExpeditionReportService $service) {}

    public function __invoke(ReportSummaryRequest $request): JsonResponse
    {
        $collection = $this->service->summary($request->input('region'));

        return response()->json(ExpeditionReportResource::collection($collection));
    }
}
