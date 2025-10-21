<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CalibrationStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'marine_lab_id' => ['required', 'integer', 'exists:marine_labs,id'],
            'instrument' => ['required', 'string'],
            'drift_ppm' => ['required', 'numeric'],
            'validated_at' => ['required', 'date'],
            'payload' => ['array'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
