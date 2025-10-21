<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpeditionReportStoreRequest;
use App\Http\Resources\ExpeditionReportDetailResource;
use App\Models\MarineLab;
use App\Services\ExpeditionReportCreateService;
use Illuminate\Http\JsonResponse;

class ExpeditionReportController extends Controller
{
    public function __construct(private ExpeditionReportCreateService $service) {}

    public function store(ExpeditionReportStoreRequest $request): JsonResponse
    {
        $lab = MarineLab::query()->where('alias', $request->input('lab_alias'))->firstOrFail();
        $report = $this->service->create($lab, $request->validated());

        return response()->json(ExpeditionReportDetailResource::make($report), 201);
    }
}
