<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportSummaryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'region' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
