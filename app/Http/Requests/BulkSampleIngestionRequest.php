<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkSampleIngestionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lab_alias' => ['required', 'string', 'exists:marine_labs,alias'],
            'batches' => ['sometimes', 'integer', 'min:1'],
            'iterations' => ['sometimes', 'integer', 'min:1000'],
            'particle_count' => ['sometimes', 'integer', 'min:1'],
            'depth_start' => ['sometimes', 'integer', 'min:0'],
            'temperature' => ['sometimes', 'numeric', 'min:-4'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
