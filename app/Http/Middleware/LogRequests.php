<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Services\LogService;

class LogRequests
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // prepare and log incoming request data to log file
        $requestPayload = $this->logService->prepareRequestData($request);
        $this->logService->logRequest('log incoming request:', $requestPayload);

        // process middleware to return response object
        $response = $next($request);

        // cast response object to array
        $responseArray = $response->getData(true);

        // validating response checking for validation errors
        $validatedResponse = $this->validateResponse($responseArray);

        // log request response 
        $this->logService->logResponse($response, $request, $validatedResponse);

        return $response;
    }

    // validating response to check for error
    protected function validateResponse(array $response): array
    {
        $error = [];
        $error['has_error'] = false;

        if (isset($response['errors'])) {
            $error['has_error'] = true;
            $error['error_message'] = $response['errors'];
        }

        return $error;
    }
}
