<?php

namespace App\Http\Services;

use App\Models\Task;

class DestroyService
{
    public function __invoke(Task $task)
    {
        $task->delete();
    }
}
