<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MicroplasticSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'marine_lab_id',
        'batch',
        'particle_count',
        'depth_meters',
        'temperature_celsius',
        'collected_at',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(MarineLab::class, 'marine_lab_id');
    }
}
