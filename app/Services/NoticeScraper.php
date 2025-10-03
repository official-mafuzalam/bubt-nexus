<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use DOMDocument;
use DOMXPath;

class NoticeScraper
{
    protected string $baseUrl = 'https://www.bubt.edu.bd';

    /**
     * Fetch and parse notices from the remote page.
     *
     * @return array<int, array{title:string, link:string, category:string, published_at:string}>
     * @throws \Exception
     */
    public function getAll(): array
    {
        $url = $this->baseUrl . '/Home/all_notice';

        $response = Http::get($url);

        if (! $response->ok()) {
            throw new \Exception("Failed to fetch notices. HTTP status: {$response->status()}");
        }

        $html = $response->body();

        // parse HTML safely (handle encoding)
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        // adjust query to the table id you showed: dtNoticeTable
        $rows = $xpath->query('//table[@id="dtNoticeTable"]//tbody/tr');

        $notices = [];

        foreach ($rows as $row) {
            $cols = $row->getElementsByTagName('td');
            if ($cols->length < 3) {
                continue;
            }

            // title + link
            $a = $cols->item(0)->getElementsByTagName('a')->item(0);
            if (! $a) continue;

            $rawTitle = trim($a->textContent ?? '');
            // Normalize whitespace inside title
            $title = preg_replace('/\s+/', ' ', $rawTitle);

            $href = $a->getAttribute('href') ?? '';
            // make absolute link
            if (strpos($href, 'http') !== 0) {
                $href = rtrim($this->baseUrl, '/') . '/' . ltrim($href, '/');
            }

            $category = trim($cols->item(1)->textContent ?? '');
            $date = trim($cols->item(2)->textContent ?? '');

            $notices[] = [
                'title' => $title,
                'link' => $href,
                'category' => $category,
                'published_at' => $date,
            ];
        }

        return $notices;
    }
}
