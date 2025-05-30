<?php

namespace App\Http\Services;

use App\Models\Task;

class StoreService
{
    public function __invoke(array $data): Task
    {
        return Task::create([
            'name' => $data['name'],
            'description' => $data['description']
        ]);
    }
}
