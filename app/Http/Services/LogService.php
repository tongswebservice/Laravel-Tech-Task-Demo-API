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
    protected function getRequestPayload(Request $request): array
    {
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return [];
        }

        return $request->json()->all() ?: [];
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

    // check if request method is allowed for id lookup 
    protected function getRequestMethod(Request $request): array
    {
        switch ($request->method()) {
            case "PATCH":
            case "PUT":
                $methodAllowed = true;
                $method = 'Update';
                break;
            case "DELETE":
                $methodAllowed = true;
                $method = 'Delete';
                break;
            default:
                $methodAllowed = false;
                $method = 'Fetch';
                break;
        }

        return [
            'allowed' => $methodAllowed,
            'method' => $method
        ];
    }

    // to log and return json response for mismatching ID
    public function logForMissingModelRequests(Request $request): JsonResponse
    {
        // prepare and log incoming request
        $requestPayload = $this->prepareRequestData($request);
        $this->logRequest('log incoming request:', $requestPayload);

        // get ID from path URL and log error 
        $id = $this->getModelIdFromUrlPath($request);
        $method = $this->getRequestMethod($request);

        if (!$method['allowed']) {
            return response()->json(['status' => true], 200);
        }

        // proceed with id lookup and render error for failed validation (if request method is allowed) 

        $errorMessage = "Failed to ${method['method']} the task due to mismatching ID of ${id}";

        Log::error('Request completed:', ['error' => $errorMessage]);

        return response()->json([
            'status' => false,
            'message' => $errorMessage,
        ], 500);
    }
}
