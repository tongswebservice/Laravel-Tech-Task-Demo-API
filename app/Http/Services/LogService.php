<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class LogService
{
    // consolidate request payload
    public function prepareRequestData(Request $request): array
    {
        return [
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_data' => $this->getRequestPayload($request)
        ];
    }

    protected function getRequestPayload(Request $request)
    {
        // Skip for GET/HEAD/OPTIONS requests
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return null;
        }

        // Handle JSON requests
        if ($request->isJson()) {
            $payload = $request->json()->all();
        }

        return $payload ?: null;
    }

    // log request data to log file 
    public function logRequest(string $message,  array $context = []): void
    {
        try {
            Log::info($message, $context);
        } catch (Exception $e) {
            Log::error("Error in failling to log - $e->getMessage()");
        }
    }

    // log request response 
    public function logResponse(mixed $response, Request $request, array $error): void
    {
        // response context
        $context = [
            'status' => $response->status(),
            'url' => $request->fullUrl(),
        ];

        // check for error and append error to context if applicable
        $hasError = false;

        if ($error['has_error']) {
            $hasError = true;
            $context['error'] = $error;
        }

        // log context to log file
        $this->logRequest(
            $hasError ? 'Request failed' : 'Request completed',
            $context
        );
    }
}
