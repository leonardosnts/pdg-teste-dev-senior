<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SalinityRiskRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lab_alias' => ['required', 'string', 'exists:marine_labs,alias'],
            'coordinates' => ['required', 'array', 'min:1'],
            'coordinates.*.lat' => ['required', 'numeric'],
            'coordinates.*.lng' => ['required', 'numeric'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
