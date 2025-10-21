<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExpeditionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'marine_lab_id',
        'expedition_code',
        'region',
        'anomaly_score',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(MarineLab::class, 'marine_lab_id');
    }

    public function observations(): HasMany
    {
        return $this->hasMany(ExpeditionObservation::class);
    }
}
