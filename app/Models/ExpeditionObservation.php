<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpeditionObservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'expedition_report_id',
        'instrument',
        'summary',
        'sample_count',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(ExpeditionReport::class);
    }
}
