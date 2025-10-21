<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalinitySurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'marine_lab_id',
        'transect',
        'surface_psu',
        'mid_psu',
        'deep_psu',
        'surveyed_at',
    ];

    protected $casts = [
        'surveyed_at' => 'datetime',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(MarineLab::class, 'marine_lab_id');
    }
}
