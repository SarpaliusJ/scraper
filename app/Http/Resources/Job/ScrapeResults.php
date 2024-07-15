<?php

namespace App\Http\Resources\Job;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScrapeResults extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'status' => $this->resource['status'],
            'urls' => $this->resource['urls'],
            'selectors' => $this->resource['selectors'],
            'scraped_data' => $this->resource['scraped_data']
        ];
    }
}
