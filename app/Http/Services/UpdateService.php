<?php

namespace App\Http\Services;

use App\Models\Task;

class UpdateService
{
    public function __invoke(array $data, Task $task): Task
    {
        $task->update([
            'name' => $data['name'] ?? $task->name,
            'description' => $data['description'] ?? $task->description
        ]);

        return $task;
    }
}
