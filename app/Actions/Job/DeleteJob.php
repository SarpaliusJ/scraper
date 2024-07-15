<?php

namespace App\Actions\Job;

use Illuminate\Support\Facades\Redis;

class DeleteJob
{
    public function handle(string $id): void
    {
        $jobData = Redis::hgetall("job:{$id}");

        if (empty($jobData)) {
            throw new \Exception('Job not found');
        }

        Redis::del("job:{$id}");
    }
}
