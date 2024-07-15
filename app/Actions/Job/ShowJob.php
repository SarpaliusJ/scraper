<?php

namespace App\Actions\Job;

use Illuminate\Support\Facades\Redis;

class ShowJob
{
    public function handle(string $id)
    {
        $data = Redis::hgetall("job:{$id}");

        if (empty($data)) {
            throw new \Exception('Job not found');
        }

        $data['scraped_data'] = $this->decodeJson($data['scraped_data']);
        $data['urls'] = $this->decodeJson($data['urls']);
        $data['selectors'] = $this->decodeJson($data['selectors']);

        return $data;
    }

    private function decodeJson($json)
    {
        return json_decode($json ?? '[]', true);
    }
}
