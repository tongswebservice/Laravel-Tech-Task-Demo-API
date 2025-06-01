<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class TaskLogServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LogService $logService;

    public function setUp(): void
    {
        $this->logService = new LogService();
    }

    public function test_model_id_can_be_extracted_from_request_url(): void
    {
        $request = Request::create('api/tasks/2', 'GET');
        $result = $this->logService->getModelIdFromUrlPath($request);

        $this->assertEquals('2', $result);
    }

    public function test_prepareRequestData_method_can_prepare_request_data_array_from_get_request(): void
    {
        $request = Request::create(
            'api/tasks/2',
            'GET',
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_USER_AGENT' => 'PostmanRuntime/7.44.0'
            ]
        );

        $result = $this->logService->prepareRequestData($request);

        $this->assertEquals([
            'method' => 'GET',
            'url' => 'http://localhost/api/tasks/2',
            'ip' => '127.0.0.1',
            'user_agent' => 'PostmanRuntime/7.44.0',
            'request_data' => []
        ], $result);
    }

    public function test_prepareRequestData_method_can_prepare_request_data_array_from_post_request(): void
    {
        $data = [
            'name' => 'test name',
            'description' => 'test description'
        ];

        $request = Request::create(
            'api/tasks',
            'POST',
            [],
            [],
            [],
            [
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_USER_AGENT' => 'PostmanRuntime/7.44.0',
                'CONTENT_TYPE' => 'application/json'
            ],
            json_encode($data)
        );

        $result = $this->logService->prepareRequestData($request);

        $this->assertEquals([
            'method' => 'POST',
            'url' => 'http://localhost/api/tasks',
            'ip' => '127.0.0.1',
            'user_agent' => 'PostmanRuntime/7.44.0',
            'request_data' => $data
        ], $result);
    }

    public function test_log_request_method_log_successful_info(): void
    {
        $data = [
            'name' => 'test name',
            'description' => 'test description'
        ];

        Log::shouldReceive('info')
            ->once()
            ->with('log incoming request', $data);

        $this->logService->logRequest('log incoming request', $data);
    }

    public function test_log_request_method_log_error_with_exception_thrown(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->andThrow(new \Exception());

        Log::shouldReceive('error')
            ->once()
            ->with('Error in failling to log - ()');

        $this->logService->logRequest('log incoming request');
    }

    public function test_log_response_method_with_error(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Request failed', [
                'status' => 500,
                'url' => 'http://localhost/api/tasks',
                'error' => ['has_error' => true, 'message' => 'validation failed!']
            ]);

        $request = Request::create('/api/tasks', 'GET');
        $response = new JsonResponse([], 500);

        $this->logService->logResponse($response, $request, [
            'has_error' => true,
            'message' => 'Test error'
        ]);
    }

    public function test_log_response_method_without_error(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('Request completed', [
                'status' => 200,
                'url' => 'http://localhost/api/tasks'
            ]);

        $request = Request::create('/api/tasks', 'GET');
        $response = new JsonResponse([], 200);

        $this->logService->logResponse($response, $request, ['has_error' => false]);
    }

    public function test_getRequestMethod_return_the_correct_methods_for_all_request_types(): void
    {
        $requestGet = Request::create('api/tasks', 'GET');
        $result1 = $this->logService->getRequestMethod($requestGet);
        $this->assertEquals('Fetch', $result1);

        $requestPatch = Request::create('api/tasks/1', 'PATCH');
        $result2 = $this->logService->getRequestMethod($requestPatch);
        $this->assertEquals('Update', $result2);

        $requestPut = Request::create('api/tasks/1', 'PUT');
        $result3 = $this->logService->getRequestMethod($requestPut);
        $this->assertEquals('Update', $result3);

        $requestDelete = Request::create('api/tasks/1', 'DELETE');
        $result4 = $this->logService->getRequestMethod($requestDelete);
        $this->assertEquals('Delete', $result4);
    }

    public function test_logForMissingModelRequests_on_missing_model_request(): void
    {
        $this->createApplication();

        $error = 'Failed to Fetch the task due to mismatching ID of 10';
        $request = Request::create('api/tasks/10', 'GET');

        Log::shouldReceive('info')
            ->once()
            ->with('log incoming request:', Mockery::any());

        Log::shouldReceive('error')
            ->once()
            ->with('Request completed:', ['error' => $error]);

        $response = $this->logService->logForMissingModelRequests($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->status());
        $this->assertEquals([
            'status' => false,
            'message' => $error
        ], $response->getData(true));
    }
}
