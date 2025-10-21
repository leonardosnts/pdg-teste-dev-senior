<?php

namespace App\Http\Controllers;

use App\Http\Requests\AlertDispatchRequest;
use App\Http\Resources\AlertDispatchResource;
use App\Models\AlertEvent;
use App\Models\MarineLab;
use App\Services\AlertDispatchService;
use Illuminate\Http\JsonResponse;

class AlertDispatchController extends Controller
{
    public function __construct(private AlertDispatchService $service) {}

    public function __invoke(AlertDispatchRequest $request): JsonResponse
    {
        $lab = MarineLab::query()->where('alias', $request->input('lab_alias'))->firstOrFail();
        $event = AlertEvent::create([
            'marine_lab_id' => $lab->id,
            'event_type' => $request->input('event_type'),
            'payload' => $request->input('payload'),
            'triggered_at' => $request->input('triggered_at'),
        ]);

        $dispatches = $this->service->dispatch($lab, $event);

        return response()->json(AlertDispatchResource::make($dispatches));
    }
}
