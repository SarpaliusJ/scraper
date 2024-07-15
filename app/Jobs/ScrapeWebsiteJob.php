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
use Symfony\Component\DomCrawler\Crawler;

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
            $this->scrapeUrl($client, $url, $scrapedData);
        }

        Redis::hset("job:{$this->jobId}", 'scraped_data', json_encode($scrapedData));
        Redis::hset("job:{$this->jobId}", 'status', 'completed');
    }

    private function scrapeUrl(Client $client, string $url, array &$scrapedData): void
    {
        try {
            $response = $client->request('GET', $url);

            if ($response->getStatusCode() === 200) {
                $html = $response->getBody()->getContents();
                $crawler = new Crawler($html);
                $this->extractContent($crawler, $url, $scrapedData);
            }
        } catch (RequestException $e) {
            Log::error('Request failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function extractContent(Crawler $crawler, string $url, array &$scrapedData): void
    {
        foreach ($this->selectors as $selector) {
            $elements = $crawler->filter($selector);
            $content = [];

            foreach ($elements as $element) {
                $content[] = $element->textContent;
            }

            $scrapedData[] = [
                'url' => $url,
                'selector' => $selector,
                'content' => $content,
            ];
        }
    }
}
