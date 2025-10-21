<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstrumentCalibration extends Model
{
    use HasFactory;

    protected $fillable = [
        'marine_lab_id',
        'instrument',
        'drift_ppm',
        'validated_at',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'validated_at' => 'datetime',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(MarineLab::class, 'marine_lab_id');
    }
}
