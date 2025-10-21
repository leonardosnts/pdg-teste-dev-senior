<?php

namespace App\Http\Controllers;

use App\Http\Requests\AcousticTelemetryRequest;
use App\Http\Requests\OpticalTelemetryRequest;
use App\Http\Resources\AcousticTelemetryResource;
use App\Http\Resources\OpticalTelemetryResource;
use App\Models\MarineLab;
use App\Services\AcousticTelemetryService;
use App\Services\OpticalTelemetryService;
use Illuminate\Http\JsonResponse;

class TelemetryController extends Controller
{
    public function __construct(
        private AcousticTelemetryService $acoustic,
        private OpticalTelemetryService $optical,
    ) {}

    public function acoustic(AcousticTelemetryRequest $request): JsonResponse
    {
        $lab = MarineLab::query()->where('alias', $request->input('lab_alias'))->firstOrFail();
        $record = $this->acoustic->ingest($lab, $request->validated());

        return response()->json(AcousticTelemetryResource::make($record));
    }

    public function optical(OpticalTelemetryRequest $request): JsonResponse
    {
        $lab = MarineLab::query()->where('alias', $request->input('lab_alias'))->firstOrFail();
        $record = $this->optical->ingest($lab, $request->validated());

        return response()->json(OpticalTelemetryResource::make($record));
    }
}
