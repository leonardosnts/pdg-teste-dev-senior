<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AcousticTelemetryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lab_alias' => ['required', 'string', 'exists:marine_labs,alias'],
            'beacon_id' => ['required', 'string'],
            'signal_strength' => ['required', 'numeric'],
            'battery_percent' => ['required', 'numeric'],
            'captured_at' => ['sometimes', 'date'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
