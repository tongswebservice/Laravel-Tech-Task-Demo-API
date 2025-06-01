<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Task;
use App\Http\Services\IndexService;
use App\Http\Services\StoreService;
use App\Http\Services\UpdateService;
use App\Http\Services\DestroyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_service_returns_no_tasks(): void
    {
        $indexService = new IndexService();
        $result = $indexService();

        $this->assertCount(0, $result);
    }

    public function test_index_service_returns_all_tasks(): void
    {
        $data = [
            'name' => 'test name',
            'description' => 'test description'
        ];

        Task::create($data);

        $indexService = new IndexService();
        $result = $indexService();

        $this->assertCount(1, $result);
        $this->assertDatabaseHas('tasks', $data);
    }

    public function test_store_service_can_create_a_task(): void
    {
        $storeService = new StoreService();

        $data = [
            'name' => 'test name1',
            'description' => 'test description1'
        ];

        $this->assertEquals(0, Task::count());

        $task = $storeService($data);
        $this->assertEquals(1, $task->count());
        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('test name1', $task->first()->name);
        $this->assertEquals('test description1', $task->first()->description);
    }

    public function test_update_service_can_update_a_task(): void
    {
        $data = [
            'name' => 'test name2',
            'description' => 'test description2'
        ];

        $task = Task::create($data);

        $this->assertEquals(1, $task->count());
        $this->assertDatabaseHas('tasks', $data);

        $updatedData = [
            'name' => 'new name',
            'description' => 'new description'
        ];

        $updateService = new UpdateService();
        $updatedTask = $updateService($updatedData, $task);

        $this->assertEquals(1, $updatedTask->count());
        $this->assertEquals('new name', $updatedTask->name);
        $this->assertEquals('new description', $updatedTask->description);
        $this->assertInstanceOf(Task::class, $updatedTask);
        $this->assertDatabaseHas('tasks', $updatedData);
        $this->assertDatabaseMissing('tasks', $data);
    }

    public function test_destroy_service_can_soft_delete_a_task(): void
    {
        $data = [
            'name' => 'test name3',
            'description' => 'test description3'
        ];

        $task = Task::create($data);

        $this->assertNull($task->deleted_at);
        $this->assertEquals(1, $task->count());

        $destroyService = new DestroyService();
        $destroyService($task);

        $this->assertEquals(0, $task->count());
        $this->assertNotNull($task->deleted_at);
        $this->assertDatabaseHas('tasks', [
            'deleted_at' => $task->deleted_at
        ]);
    }
}
