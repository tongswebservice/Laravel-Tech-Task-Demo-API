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
        $requestPayload = $this->logService->prepareRequestData($request);

        $this->logService->logRequest('log incoming request:', $requestPayload);

        $response = $next($request);

        $this->logService->logResponse($response, $request);

        return $response;
    }
}
