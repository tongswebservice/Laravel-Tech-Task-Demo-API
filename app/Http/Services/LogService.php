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
            'user_agent' => $request->userAgent()
        ];
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
    public function logResponse(mixed $response, Request $request): void
    {
        $this->logRequest('log request response:', [
            'status' => $response->status(),
            'url' => $request->fullUrl(),
        ]);
    }
}
