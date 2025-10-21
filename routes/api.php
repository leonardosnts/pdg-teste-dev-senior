<?php

use App\Http\Controllers\AlertDispatchController;
use App\Http\Controllers\BulkSampleIngestionController;
use App\Http\Controllers\CalibrationController;
use App\Http\Controllers\ExpeditionReportController;
use App\Http\Controllers\ExpeditionSummaryController;
use App\Http\Controllers\SalinityRiskController;
use App\Http\Controllers\TelemetryController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::get('/', fn() => response()->json(['message' => 'PDG Backend - API only'], 200));

    Route::post('marine-labs/ingest-samples', BulkSampleIngestionController::class);
    Route::get('expeditions/summary', ExpeditionSummaryController::class);
    Route::post('expeditions', [ExpeditionReportController::class, 'store']);
    Route::post('calibrations', [CalibrationController::class, 'store']);
    Route::post('salinity/assess', SalinityRiskController::class);
    Route::post('telemetry/acoustic', [TelemetryController::class, 'acoustic']);
    Route::post('telemetry/optical', [TelemetryController::class, 'optical']);
    Route::post('alerts/dispatch', AlertDispatchController::class);
});
