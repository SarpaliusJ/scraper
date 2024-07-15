<?php

namespace App\Jobs;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ScrapeWebsiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $jobId;
    protected array $urls;
    protected array $selectors;

    /**
     * Create a new job instance.
     */
    public function __construct(string $jobId, array $urls, array $selectors)
    {
        $this->jobId = $jobId;
        $this->urls = $urls;
        $this->selectors = $selectors;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = new Client();

        Redis::hset("job:{$this->jobId}", 'status', 'in_progress');

        $scrapedData = [];

        foreach ($this->urls as $url) {
            try {
                $response = $client->request('GET', $url);

                if ($response->getStatusCode() == 200) {
                    $html = $response->getBody()->getContents();

                    foreach ($this->selectors as $selector) {
                        $pattern = $this->getPatternForSelector($selector);
                        preg_match_all($pattern, $html, $matches);
                        $content = $matches[1] ?? [];

                        $scrapedData[] = [
                            'url' => $url,
                            'selector' => $selector,
                            'content' => $content,
                        ];
                    }
                }
            } catch (RequestException $e) {
                Log::error('Request failed', [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Redis::hset("job:{$this->jobId}", 'scraped_data', json_encode($scrapedData));
        Redis::hset("job:{$this->jobId}", 'status', 'completed');
    }

    private function getPatternForSelector($selector): string
    {
        $pattern = '';

        if (preg_match('/^\.(.+)$/', $selector, $matches)) {
            $pattern = '/<[^>]*class="[^"]*' . preg_quote($matches[1], '/') . '[^"]*"[^>]*>(.*?)<\/[^>]*>/';
        } elseif (preg_match('/^#(.+)$/', $selector, $matches)) {
            $pattern = '/<[^>]*id="' . preg_quote($matches[1], '/') . '"[^>]*>(.*?)<\/[^>]*>/';
        } else {
            $pattern = '/<' . preg_quote($selector, '/') . '[^>]*>(.*?)<\/' . preg_quote($selector, '/') . '>/';
        }

        return $pattern;
    }
}
