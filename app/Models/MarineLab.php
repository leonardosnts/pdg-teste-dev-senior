<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarineLab extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'alias',
        'ocean_basin',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function samples(): HasMany
    {
        return $this->hasMany(MicroplasticSample::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ExpeditionReport::class);
    }

    public function calibrations(): HasMany
    {
        return $this->hasMany(InstrumentCalibration::class);
    }

    public function salinitySurveys(): HasMany
    {
        return $this->hasMany(SalinitySurvey::class);
    }

    public function acousticReadings(): HasMany
    {
        return $this->hasMany(AcousticTelemetryReading::class);
    }

    public function opticalReadings(): HasMany
    {
        return $this->hasMany(OpticalTelemetryReading::class);
    }

    public function alertChannels(): HasMany
    {
        return $this->hasMany(AlertChannel::class);
    }

    public function alertEvents(): HasMany
    {
        return $this->hasMany(AlertEvent::class);
    }
}
