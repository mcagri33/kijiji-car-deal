<?php

declare(strict_types=1);

namespace App\Services\Kijiji;

use App\Models\KijijiFilter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Lightweight, human-like Kijiji scraper service.
 * - Random delay 2-4 seconds between requests
 * - Max 100 listings per filter
 * - No parallel scraping
 * - Graceful failure
 */
class KijijiScraperService
{
    private const MAX_LISTINGS = 100;

    private const USER_AGENTS = [
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    ];

    /**
     * Build search URL from filter criteria.
     * Uses Kijiji Autos Canada URL structure.
     */
    public function buildUrlFromFilter(KijijiFilter $filter): string
    {
        $base = 'https://www.kijijiautos.ca/cars/' . strtolower($filter->location) . '/';
        $params = [];

        if ($filter->make) {
            $params['make'] = $filter->make;
        }
        if ($filter->model) {
            $params['model'] = $filter->model;
        }
        if ($filter->min_price !== null) {
            $params['minPrice'] = $filter->min_price;
        }
        if ($filter->max_price !== null) {
            $params['maxPrice'] = $filter->max_price;
        }
        if ($filter->min_year !== null) {
            $params['minYear'] = $filter->min_year;
        }
        if ($filter->max_year !== null) {
            $params['maxYear'] = $filter->max_year;
        }
        if ($filter->max_km !== null) {
            $params['maxMileage'] = $filter->max_km;
        }

        $query = http_build_query($params);

        return $query ? $base . '?' . $query : $base;
    }

    /**
     * Fetch HTML from URL with human-like behavior.
     */
    public function fetchHtml(string $url): ?string
    {
        $result = $this->fetchWithStatus($url);

        return $result['html'];
    }

    /**
     * Fetch listing page and return HTML + status. Used for sold detection.
     * Returns ['html' => ?string, 'status' => 'active'|'sold'].
     */
    public function fetchListingPage(string $url): array
    {
        $result = $this->fetchWithStatus($url);

        if ($result['status'] === 'sold') {
            return $result;
        }

        if ($result['html']) {
            $result['status'] = $this->parseSingleListingStatus($result['html']);
        }

        return $result;
    }

    /**
     * Internal fetch with status detection (404 = sold).
     */
    private function fetchWithStatus(string $url): array
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENTS[array_rand(self::USER_AGENTS)],
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-CA,en;q=0.9',
            ])
                ->timeout(15)
                ->retry(2, 1000)
                ->get($url);

            if ($response->status() === 404) {
                Log::channel('kijiji')->info('Listing 404 (likely sold)', ['url' => $url]);

                return ['html' => null, 'status' => 'sold'];
            }

            if (! $response->successful()) {
                Log::channel('kijiji')->warning('Kijiji fetch failed', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return ['html' => null, 'status' => 'active'];
            }

            sleep(random_int(2, 4));

            return ['html' => $response->body(), 'status' => 'active'];
        } catch (\Throwable $e) {
            Log::channel('kijiji')->error('Kijiji fetch exception', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return ['html' => null, 'status' => 'active'];
        }
    }

    /**
     * Parse listings from search results HTML.
     * Returns array of [external_id, title, price, url].
     * Does NOT crash on single parsing failure.
     */
    public function parseListings(string $html): array
    {
        $listings = [];
        $count = 0;

        try {
            // Kijiji Autos uses data attributes and structured markup
            // Fallback: extract listing links and basic info via regex (lightweight)
            if (preg_match_all(
                '/data-listing-id="([^"]+)"[^>]*>.*?<a[^>]+href="([^"]+)"[^>]*>([^<]+)<\/a>.*?(\$[\d,]+)/s',
                $html,
                $matches,
                PREG_SET_ORDER
            )) {
                foreach ($matches as $match) {
                    if ($count >= self::MAX_LISTINGS) {
                        break;
                    }
                    try {
                        $externalId = trim($match[1]);
                        $url = $this->normalizeUrl($match[2]);
                        $title = trim(strip_tags($match[3]));
                        $price = (int) preg_replace('/[^0-9]/', '', $match[4]);
                        if ($externalId && $url && $title && $price > 0) {
                            $listings[] = [
                                'external_id' => $externalId,
                                'title' => $title,
                                'price' => $price,
                                'url' => $url,
                            ];
                            $count++;
                        }
                    } catch (\Throwable) {
                        continue;
                    }
                }
            }

            // Alternative: JSON-LD or script data (Kijiji often embeds data)
            if (empty($listings) && preg_match('/"listingId"\s*:\s*"([^"]+)"[^}]*"url"\s*:\s*"([^"]+)"[^}]*"title"\s*:\s*"([^"]+)"[^}]*"price"\s*:\s*(\d+)/', $html, $jsonMatch)) {
                // Single match - try to get more via global pattern
                preg_match_all('/"listingId"\s*:\s*"([^"]+)"[^}]*"url"\s*:\s*"([^"]+)"[^}]*"title"\s*:\s*"([^"]+)"[^}]*"price"\s*:\s*(\d+)/', $html, $jsonMatches, PREG_SET_ORDER);
                foreach ($jsonMatches ?? [] as $m) {
                    if ($count >= self::MAX_LISTINGS) {
                        break;
                    }
                    try {
                        $listings[] = [
                            'external_id' => $m[1],
                            'title' => stripslashes($m[3]),
                            'price' => (int) $m[4],
                            'url' => $this->normalizeUrl($m[2]),
                        ];
                        $count++;
                    } catch (\Throwable) {
                        continue;
                    }
                }
            }

            // Fallback: simple link extraction for kijiji.ca / kijijiautos.ca
            if (empty($listings) && preg_match_all('/href="(\/cars\/[^"]+\/v\d+c\d+d\d+[^"]*)"[^>]*>([^<]{10,100})<\/a>/', $html, $linkMatches, PREG_SET_ORDER)) {
                foreach ($linkMatches as $lm) {
                    if ($count >= self::MAX_LISTINGS) {
                        break;
                    }
                    $url = 'https://www.kijijiautos.ca' . $lm[1];
                    $externalId = $this->extractExternalIdFromUrl($url);
                    if ($externalId) {
                        $listings[] = [
                            'external_id' => $externalId,
                            'title' => trim(strip_tags($lm[2])),
                            'price' => 0, // Will need second pass - use placeholder
                            'url' => $url,
                        ];
                        $count++;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::channel('kijiji')->error('Parse listings failed', ['message' => $e->getMessage()]);
        }

        return array_slice($listings, 0, self::MAX_LISTINGS);
    }

    /**
     * Parse single listing page to determine status (active/sold).
     */
    public function parseSingleListingStatus(string $html): string
    {
        $soldIndicators = [
            'This listing is no longer available',
            'no longer available',
            'listing has been deleted',
            'Page not found',
            '404',
        ];

        $lower = strtolower($html);
        foreach ($soldIndicators as $indicator) {
            if (str_contains($lower, strtolower($indicator))) {
                return 'sold';
            }
        }

        return 'active';
    }

    private function normalizeUrl(string $url): string
    {
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        if (str_starts_with($url, '/')) {
            return 'https://www.kijijiautos.ca' . $url;
        }

        return $url;
    }

    private function extractExternalIdFromUrl(string $url): ?string
    {
        if (preg_match('/[\/\-]v(\d+)c(\d+)d(\d+)/', $url, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3];
        }
        if (preg_match('/\/(\d+)(?:\?|$)/', $url, $m)) {
            return $m[1];
        }

        return null;
    }
}
