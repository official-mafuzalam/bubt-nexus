<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class NoticeResource extends JsonResource
{
    public function toArray($request): array
    {
        $rawDate = $this['published_at'] ?? ($this->published_at ?? null);
        $iso = null;

        if ($rawDate) {
            // try d M Y e.g. "30 Sep 2025" -> to ISO; fallback to parse
            try {
                $iso = Carbon::createFromFormat('d M Y', trim($rawDate))->toIso8601String();
            } catch (\Throwable $e) {
                try {
                    $iso = Carbon::parse($rawDate)->toIso8601String();
                } catch (\Throwable $e) {
                    $iso = null;
                }
            }
        }

        return [
            'title' => $this['title'] ?? $this->title ?? null,
            'link' => $this['link'] ?? $this->link ?? null,
            'category' => $this['category'] ?? $this->category ?? null,
            'published_at' => $rawDate,
            'published_at_iso' => $iso,
        ];
    }
}
