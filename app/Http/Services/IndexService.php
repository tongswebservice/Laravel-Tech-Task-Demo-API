<?php

namespace App\Http\Services;

use App\Models\Task;

class IndexService
{
    public function __invoke()
    {
        return Task::get();
    }
}
