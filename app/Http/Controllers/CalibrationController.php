<?php

namespace App\Http\Controllers;

use App\Http\Requests\CalibrationStoreRequest;
use App\Http\Resources\CalibrationResource;
use App\Services\CalibrationRegistry;
use Illuminate\Http\JsonResponse;

class CalibrationController extends Controller
{
    public function __construct(private CalibrationRegistry $registry) {}

    public function store(CalibrationStoreRequest $request): JsonResponse
    {
        $calibration = $this->registry->store($request->validated());

        return response()->json(CalibrationResource::make($calibration));
    }
}
