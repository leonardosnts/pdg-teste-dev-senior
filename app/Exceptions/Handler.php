<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        // keep defaults empty here
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            try {
                Log::error($e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString(),
                ]);
            } catch (Throwable $ex) {
                // avoid throwing while reporting
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        if ($request->is('api/*') || $request->expectsJson()) {
            try {
                Log::error('API Exception: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            } catch (Throwable $ex) {
                // ignore
            }

            $status = 500;
            if (method_exists($e, 'getStatusCode')) {
                try {
                    /** @var \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e */
                    $status = $e->getStatusCode();
                } catch (Throwable $t) {
                    $status = 500;
                }
            }

            $payload = [
                'message' => $e->getMessage() ?: 'Server Error',
            ];

            if (config('app.debug')) {
                $payload['exception'] = get_class($e);
                $payload['file'] = $e->getFile();
                $payload['line'] = $e->getLine();
                $payload['trace'] = collect($e->getTrace())->map(function ($trace) {
                    return array_filter($trace, function ($key) {
                        return !in_array($key, ['args']);
                    }, ARRAY_FILTER_USE_KEY);
                })->take(10)->toArray();
            }

            return response()->json($payload, $status, [
                'Content-Type' => 'application/json',
            ]);
        }

        return parent::render($request, $e);
    }
}
