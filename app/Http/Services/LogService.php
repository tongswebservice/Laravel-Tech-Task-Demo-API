<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\JsonResponse;

class LogService
{
    // get model id from URL path
    protected function getModelIdFromUrlPath(Request $request): string
    {
        $path = $request->path();
        $segments = explode('/', $path);
        return (string) end($segments);
    }

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

    // get request payload for GET/HEAD/OPTIONS requests 
    protected function getRequestPayload(Request $request): ?array
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

    // to log and return json response for mismatching ID
    public function logForMissingModelRequests(Request $request): JsonResponse
    {
        // prepare and log incoming request
        $requestPayload = $this->prepareRequestData($request);
        $this->logRequest('log incoming request:', $requestPayload);

        // get ID from path URL and log error 
        $id = $this->getModelIdFromUrlPath($request);
        Log::error('Request completed:', ['error' => "Failed to update the task due to mismatching ID of ${id}"]);

        // return json response with error
        return response()->json([
            'status' => false,
            'message' => "Failed to update the task due to mismatching ID of ${id}",
        ], 500);
    }
}
