<?php

namespace App\Http\Controllers;

use App\Http\Requests\SalinityRiskRequest;
use App\Http\Resources\SalinityRiskResource;
use App\Models\MarineLab;
use App\Services\SalinityRiskService;
use Illuminate\Http\JsonResponse;

class SalinityRiskController extends Controller
{
    public function __construct(private SalinityRiskService $service) {}

    public function __invoke(SalinityRiskRequest $request): JsonResponse
    {
        $lab = MarineLab::query()->where('alias', $request->input('lab_alias'))->firstOrFail();
        $assessment = $this->service->assess($lab, $request->input('coordinates'));

        return response()->json(SalinityRiskResource::make($assessment));
    }
}
