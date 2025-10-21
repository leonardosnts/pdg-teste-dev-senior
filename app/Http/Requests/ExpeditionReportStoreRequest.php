<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpeditionReportStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lab_alias' => ['required', 'string', 'exists:marine_labs,alias'],
            'expedition_code' => ['required', 'string', 'unique:expedition_reports,expedition_code'],
            'region' => ['required', 'string'],
            'anomaly_score' => ['required', 'integer', 'min:0', 'max:100'],
            'metadata' => ['sometimes', 'array'],
            'observations' => ['required', 'array', 'min:1'],
            'observations.*.instrument' => ['required', 'string'],
            'observations.*.summary' => ['required', 'string'],
            'observations.*.sample_count' => ['required', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
