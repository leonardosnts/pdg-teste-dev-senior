<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'marine_lab_id',
        'channel',
        'endpoint',
        'constraints',
    ];

    protected $casts = [
        'constraints' => 'array',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(MarineLab::class, 'marine_lab_id');
    }
}
