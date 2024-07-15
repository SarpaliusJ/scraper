<?php

namespace App\Actions\Job;

use App\Jobs\ScrapeWebsiteJob as ScrapeWebsite;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class StoreJob
{
    public function handle($data): string
    {
        $jobId = Str::uuid()->toString();

        $jobData = [
            'id' => $jobId,
            'status' => 'queued',
            'urls' => json_encode($data['urls']),
            'selectors' => json_encode($data['selectors']),
            'created_at' => now()->toDateTimeString(),
        ];

        Redis::hmset("job:{$jobId}", $jobData);

        ScrapeWebsite::dispatch($jobId, $data['urls'], $data['selectors']);

        return $jobId;
    }
}
