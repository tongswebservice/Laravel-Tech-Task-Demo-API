<?php

namespace App\Http\Services;

use App\Models\Task;
use Illuminate\Support\Facades\Log;

class IndexService
{
    public function __invoke()
    {
        return Task::get();
    }
}
