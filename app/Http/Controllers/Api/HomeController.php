<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NoticeScraper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\NoticeResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HomeController extends Controller
{
    protected NoticeScraper $scraper;

    public function __construct(NoticeScraper $scraper)
    {
        $this->scraper = $scraper;
    }

    public function notices(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $cacheKey = 'bubt:notices';
        $cacheTtlMinutes = 60; // cache for 60 minutes

        try {
            // Cache all notices
            $notices = Cache::remember($cacheKey, $cacheTtlMinutes, function () {
                return $this->scraper->getAll();
            });

            // Filter last 30 days
            $notices = collect($notices)->filter(function ($notice) {
                try {
                    $date = Carbon::createFromFormat('d M Y', trim($notice['published_at']));
                } catch (\Throwable $e) {
                    return false; // skip if date is invalid
                }

                return $date->greaterThanOrEqualTo(Carbon::now()->subDays(60));
            })->values();

        } catch (\Throwable $e) {
            Log::error('Notice scraping failed: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to fetch notices right now. Please try again later.'
            ], 502);
        }

        return NoticeResource::collection($notices);
    }
}
