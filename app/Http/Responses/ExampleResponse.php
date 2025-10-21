<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ExampleResponse extends JsonResponse
{
    public static function success($data = [], $status = 200): self
    {
        return new self(['data' => $data], $status);
    }
}
