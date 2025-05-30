<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Services\LogService;
use App\Http\Services\IndexService;
use App\Http\Services\StoreService;


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(IndexService $indexService): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Successfully fetched the tasks.',
            'data' => $indexService()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request, StoreService $storeService, LogService $logService): JsonResponse
    {
        try {
            $data = $storeService($request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Successfully stored the task.',
                'data' => $data
            ]);
        } catch (Exception $e) {

            $response = response()->json([
                'status' => false,
                'message' => 'Failed to store the task.',
            ], 500);

            $logService->logResponse($response, $request, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
}
