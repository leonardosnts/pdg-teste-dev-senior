<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpticalTelemetryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lab_alias' => ['required', 'string', 'exists:marine_labs,alias'],
            'camera_id' => ['required', 'string'],
            'clarity_index' => ['required', 'numeric'],
            'battery_percent' => ['required', 'numeric'],
            'captured_at' => ['sometimes', 'date'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
