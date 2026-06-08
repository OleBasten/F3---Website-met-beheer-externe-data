<?php

$api_key        = '41860cc627c14bb1b0b4f92da537a95c';
$publisher_name = 'RAWG';
$games          = [];
$publisher_info = null;
$error          = '';
$total_count    = 0;
$page           = max(1, (int)($_GET['page'] ?? 1));
$page_size      = 12;
$next_page      = null;
$prev_page      = null;

function rawg_get(string $url): array {
    $ctx = stream_context_create(['http' => [
        'timeout' => 10,
        'header'  => "User-Agent: PHP-RAWG-Filter/1.0\r\n",
    ]]);
    $body = @file_get_contents($url, false, $ctx);
    if ($body === false) {
        throw new RuntimeException('Kan de RAWG API niet bereiken. Controleer je internetverbinding.');
    }
    $data = json_decode($body, true);
    if (!is_array($data)) {
        throw new RuntimeException('Ongeldig antwoord van de RAWG API.');
    }
    if (isset($data['detail'])) {
        throw new RuntimeException('API-fout: ' . $data['detail']);
    }
    return $data;
}

function rawg_find_publisher(string $api_key, string $name): array {
    $url  = 'https://api.rawg.io/api/publishers?key=' . urlencode($api_key)
        . '&search=' . urlencode($name) . '&page_size=5';
    $data = rawg_get($url);
    if (empty($data['results'])) {
        throw new RuntimeException("Publisher \"$name\" niet gevonden.");
    }
    return $data['results'][0]; // ['id', 'name', 'games_count', ...]
}

function rawg_get_games(string $api_key, int $publisher_id, int $page, int $page_size): array {
    $url = 'https://api.rawg.io/api/games?key=' . urlencode($api_key)
        . '&publishers=' . $publisher_id
        . '&page=' . $page
        . '&page_size=' . $page_size
        . '&ordering=-rating';
    return rawg_get($url);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_key        = trim($_POST['api_key']        ?? '');
    $publisher_name = trim($_POST['publisher_name'] ?? '');
    // Bewaar waarden in de URL zodat paginering werkt
    if ($api_key && $publisher_name) {
        header('Location: ?api_key=' . urlencode($api_key)
            . '&publisher=' . urlencode($publisher_name)
            . '&page=1');
        exit;
    }
} else {
    $api_key        = trim($_GET['api_key']    ?? '');
    $publisher_name = trim($_GET['publisher']  ?? '');
}

if ($api_key && $publisher_name) {
    try {
        $publisher_info = rawg_find_publisher($api_key, $publisher_name);
        $result         = rawg_get_games($api_key, $publisher_info['id'], $page, $page_size);
        $games          = $result['results'] ?? [];
        $total_count    = $result['count']   ?? 0;
        $next_page      = $result['next']    ? $page + 1 : null;
        $prev_page      = $result['previous'] ? $page - 1 : null;
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    }
}

function stars(float $rating): string {
    $full  = (int)round($rating);
    $out   = '';
    for ($i = 1; $i <= 5; $i++) {
        $out .= $i <= $full ? '★' : '☆';
    }
    return $out;
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RAWG Publisher Filter</title>
    <link rel="stylesheet" href="../stylesheet/styling.css">
</head>
<body>
<div class="container">

    <div class="header">
        <h1>RAWG <span>Publisher</span> Filter</h1>
        <p>Zoek games op publisher naam via de RAWG API</p>
    </div>

    <form method="POST">
        <div class="field">
            <label for="api_key">API-sleutel</label>
            <input type="password" id="api_key" name="api_key"
                   placeholder="Jouw RAWG API-sleutel"
                   value="<?= htmlspecialchars($api_key) ?>"
                   required>
        </div>
        <div class="field">
            <label for="publisher_name">Publisher naam</label>
            <input type="text" id="publisher_name" name="publisher_name"
                   placeholder="bijv. Nintendo, Ubisoft, Bethesda"
                   value="<?= htmlspecialchars($publisher_name) ?>"
                   required>
        </div>
        <div class="field">
            <label>&nbsp;</label>
            <button type="submit">🔍 Zoeken</button>
        </div>
    </form>

    <?php if ($error): ?>
        <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($publisher_info && $games): ?>

        <div class="results-meta">
            <h2>Games van <strong><?= htmlspecialchars($publisher_info['name']) ?></strong></h2>
            <span><?= number_format($total_count) ?> gevonden</span>
        </div>

        <div class="games-grid">
            <?php foreach ($games as $g): ?>
                <?php
                $title  = htmlspecialchars($g['name'] ?? 'Onbekend');
                $img    = $g['background_image'] ?? '';
                $rating = isset($g['rating']) ? round((float)$g['rating'], 1) : null;
                $year   = isset($g['released']) ? substr($g['released'], 0, 4) : null;
                $genre  = $g['genres'][0]['name'] ?? null;
                ?>
                <div class="card">
                    <?php if ($img): ?>
                        <img class="card-img" src="<?= htmlspecialchars($img) ?>" alt="<?= $title ?>" loading="lazy">
                    <?php else: ?>
                        <div class="card-img-placeholder">🎮</div>
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="card-title" title="<?= $title ?>"><?= $title ?></div>
                        <div class="card-meta">
                            <?php if ($rating): ?>
                                <span class="pill pill-green">★ <?= $rating ?></span>
                                <span class="stars"><?= stars($rating) ?></span>
                            <?php endif; ?>
                            <?php if ($year): ?>
                                <span class="pill pill-muted"><?= $year ?></span>
                            <?php endif; ?>
                            <?php if ($genre): ?>
                                <span class="pill pill-muted"><?= htmlspecialchars($genre) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($prev_page || $next_page): ?>
            <?php
            $base = '?api_key=' . urlencode($api_key) . '&publisher=' . urlencode($publisher_name);
            $total_pages = (int)ceil($total_count / $page_size);
            ?>
            <div class="pagination">
                <?php if ($prev_page): ?>
                    <a href="<?= $base ?>&page=<?= $prev_page ?>">← Vorige</a>
                <?php endif; ?>
                <span class="cur">Pagina <?= $page ?> van <?= $total_pages ?></span>
                <?php if ($next_page): ?>
                    <a href="<?= $base ?>&page=<?= $next_page ?>">Volgende →</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php elseif (!$error): ?>
        <div class="empty">
            <span class="icon">🎮</span>
            Voer een API-sleutel en publisher naam in om te zoeken.
            <br><small style="font-size:.8rem;margin-top:.5rem;display:block">
                Gratis API-sleutel ophalen op <a href="https://rawg.io/apidocs" style="color:var(--accent)">rawg.io/apidocs</a>
            </small>
        </div>
    <?php endif; ?>

</div>
</body>
</html>