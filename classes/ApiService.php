<?php
require_once __DIR__ . '/../config/database.php';

/**
 * ApiService – haalt speldata op via de RAWG Video Games Database API.
 *
 * ESRB-ratings (gebruikt voor inhoudsfilter):
 *   1 = Everyone | 2 = Everyone 10+ | 3 = Teen | 4 = Mature (17+) | 5 = Adults Only (18+)
 */
class ApiService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey  = RAWG_API_KEY;
        $this->baseUrl = RAWG_BASE_URL;
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function get(string $endpoint, array $params = []): ?array
    {
        $params['key'] = $this->apiKey;
        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);

        $context = stream_context_create([
            'http' => [
                'method'  => 'GET',
                'header'  => "User-Agent: Vault/1.0\r\n",
                'timeout' => 10,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            error_log("ApiService: verzoek naar {$url} mislukt.");
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * Map een ESRB-ratingobject naar een genormaliseerde array.
     * Geeft null terug als het spel geen ESRB-rating heeft.
     */
    private function mapEsrb(?array $esrb): ?array
    {
        if (empty($esrb)) return null;
        return [
            'id'   => (int) ($esrb['id']   ?? 0),
            'slug' => $esrb['slug'] ?? '',
            'name' => $esrb['name'] ?? '',
        ];
    }

    /**
     * Geeft true terug als het spel als volwasseninhoud (18+) wordt beschouwd.
     * ESRB id >= 4 = Mature (17+) of Adults Only (18+).
     */
    public static function isMatureContent(?array $esrb): bool
    {
        return isset($esrb['id']) && $esrb['id'] >= 4;
    }

    // ── Public methodes ──────────────────────────────────────────────────────

    /**
     * Zoek games via RAWG. Geeft een array van genormaliseerde game-arrays.
     * Nu inclusief esrb_rating voor het inhoudsfilter.
     */
    public function searchGames(string $query, int $pageSize = 12): array
    {
        $data = $this->get('/games', [
            'search'    => $query,
            'page_size' => $pageSize,
        ]);

        if (!$data || empty($data['results'])) {
            return [];
        }

        return array_map(function (array $item): array {
            return [
                'rawg_id'      => $item['id']                          ?? null,
                'title'        => $item['name']                        ?? 'Onbekend',
                'release_year' => isset($item['released'])
                    ? (int) substr($item['released'], 0, 4)
                    : null,
                'rating'       => isset($item['rating'])
                    ? round((float)$item['rating'] * 2, 1)
                    : null,
                'cover_url'    => $item['background_image']            ?? null,
                'genres'       => array_column($item['genres'] ?? [], 'name'),
                'platforms'    => array_column(
                    array_column($item['platforms'] ?? [], 'platform'),
                    'name'
                ),
                'metacritic'   => $item['metacritic']                  ?? null,
                'esrb_rating'  => $this->mapEsrb($item['esrb_rating'] ?? null),
            ];
        }, $data['results']);
    }

    /**
     * Haal gedetailleerde info op voor één game via RAWG ID.
     */
    public function getGameDetails(int $rawgId): ?array
    {
        $data = $this->get("/games/{$rawgId}");

        if (!$data || isset($data['detail'])) {
            return null;
        }

        return [
            'rawg_id'      => $data['id']               ?? null,
            'title'        => $data['name']              ?? 'Onbekend',
            'description'  => strip_tags($data['description_raw'] ?? ''),
            'release_year' => isset($data['released'])
                ? (int) substr($data['released'], 0, 4)
                : null,
            'rating'       => isset($data['rating'])
                ? round((float)$data['rating'] * 2, 1)
                : null,
            'cover_url'    => $data['background_image'] ?? null,
            'genres'       => array_column($data['genres'] ?? [], 'name'),
            'platforms'    => array_column(
                array_column($data['platforms'] ?? [], 'platform'),
                'name'
            ),
            'website'      => $data['website']          ?? null,
            'metacritic'   => $data['metacritic']       ?? null,
            'esrb_rating'  => $this->mapEsrb($data['esrb_rating'] ?? null),
        ];
    }
}
