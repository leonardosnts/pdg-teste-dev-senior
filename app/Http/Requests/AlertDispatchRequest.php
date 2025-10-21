<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AlertDispatchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lab_alias' => ['required', 'string', 'exists:marine_labs,alias'],
            'event_type' => ['required', 'string'],
            'payload' => ['required', 'array'],
            'triggered_at' => ['required', 'date'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
