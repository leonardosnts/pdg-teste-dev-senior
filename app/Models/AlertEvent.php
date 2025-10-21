<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'marine_lab_id',
        'event_type',
        'payload',
        'triggered_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'triggered_at' => 'datetime',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(MarineLab::class, 'marine_lab_id');
    }
}
