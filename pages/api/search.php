<?php
session_start();

require_once '../../classes/Database.php';
require_once '../../classes/GameRepository.php';
require_once '../../classes/ApiService.php';

$pdo        = Database::getInstance();
$repository = new GameRepository($pdo);
$apiService = new ApiService();
$genres     = $repository->getAllGenres();

$query   = trim($_GET['q'] ?? '');
$results = [];
$error   = null;

// ── Zoek via RAWG API ────────────────────────────────────────
if ($query !== '') {
    $results = $apiService->searchGames($query, 12);
    if ($results === null) {
        $error   = 'Kon geen verbinding maken met de RAWG API. Vul jouw API-sleutel in via config/database.php.';
        $results = [];
    }
}

// ── Importeer een game vanuit RAWG ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rawg_id'])) {
    $rawgId  = filter_input(INPUT_POST, 'rawg_id', FILTER_VALIDATE_INT);
    $details = $rawgId ? $apiService->getGameDetails((int)$rawgId) : null;

    if ($details) {
        $genreId = null;
        if (!empty($details['genres'])) {
            foreach ($genres as $g) {
                if (in_array($g['name'], $details['genres'], true)) {
                    $genreId = $g['id'];
                    break;
                }
            }
        }

        $newId = $repository->create([
            'title'        => $details['title'],
            'description'  => $details['description'] ?? null,
            'genre_id'     => $genreId,
            'platform_id'  => null,
            'release_year' => $details['release_year'] ?? null,
            'rating'       => $details['rating']       ?? null,
            'cover_url'    => $details['cover_url']    ?? null,
            'rawg_id'      => $details['rawg_id'],
        ]);

        $_SESSION['flash'][] = [
            'type'    => 'success',
            'message' => "'{$details['title']}' is geïmporteerd vanuit de RAWG API!"
        ];
        header("Location: ../games/show.php?id={$newId}");
        exit;
    }

    $_SESSION['flash'][] = [
        'type'    => 'error',
        'message' => 'Kon de gamedetails niet ophalen. Probeer het opnieuw.'
    ];
}

$pageTitle = 'API Zoeken – Vault';
require_once '../../includes/header.php';
?>

    <!-- ── Page header ─────────────────────────────────────────── -->
    <div class="gv-page-header">
        <div class="container">
            <div class="gv-page-header-inner">
                <div>
                    <p class="gv-breadcrumb">
                        <a href="../../index.php">Home</a> / API Zoeken
                    </p>
                    <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;margin:.25rem 0 0;">
                        <i class="bi bi-cloud-arrow-down text-accent"></i> RAWG API Zoeken
                    </h1>
                    <p style="color:var(--text-secondary);font-size:.88rem;margin:.4rem 0 0;">
                    </p>
                </div>

                <a href="../games/index.php" class="gv-btn-outline gv-btn-sm">
                    <i class="bi bi-collection"></i> Mijn collectie
                </a>
            </div>

            <!-- Zoekformulier -->
            <form method="GET" class="mt-4">
                <div class="d-flex flex-wrap gap-3 align-items-end">
                    <div class="flex-grow-1" style="max-width:480px;">
                        <label class="form-label">Zoek een game via de RAWG API</label>
                        <div class="gv-search-wrap">
                            <i class="bi bi-search"></i>
                            <input type="text" name="q" class="form-control"
                                   placeholder="bijv. Elden Ring, Minecraft, GTA…"
                                   value="<?= htmlspecialchars($query) ?>"
                                   autofocus>
                        </div>
                    </div>
                    <button type="submit" class="gv-btn-primary" style="padding:.65rem 1.5rem;">
                        <i class="bi bi-search"></i> Zoeken
                    </button>
                    <?php if ($query): ?>
                        <a href="search.php" class="gv-btn-outline gv-btn-sm" style="align-self:flex-end;">
                            <i class="bi bi-x"></i> Wis
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ── Content ──────────────────────────────────────────────── -->
    <div class="container" style="padding-bottom:5rem;">

        <?php if ($error): ?>
            <!-- API fout -->
            <div class="gv-empty">
                <i class="bi bi-wifi-off" style="color:var(--danger);"></i>
                <h3>API niet bereikbaar</h3>
                <p style="max-width:420px;"><?= htmlspecialchars($error) ?></p>
                <a href="https://rawg.io/apidocs" target="_blank" rel="noopener"
                   class="gv-btn-outline mt-3">
                    <i class="bi bi-box-arrow-up-right"></i> RAWG API-sleutel ophalen
                </a>
            </div>

        <?php elseif ($query === ''): ?>
            <div class="gv-empty" style="padding-bottom:1rem;">
                <i class="bi bi-controller" style="color:var(--accent);"></i>
                <h3>Zoek games in de RAWG-database</h3>
                <p>Typ een gamenaam hierboven om live resultaten te zien.<br>
                    Importeer games met één klik in jouw eigen collectie.</p>
            </div>

            <!-- Stappenuitleg -->
            <div class="row g-3 mt-2" style="max-width:860px;margin-left:auto;margin-right:auto;">
                <?php
                $steps = [
                    ['bi-1-circle-fill', '#818cf8', 'API-call uitvoeren',
                        'Typ een zoekterm → de ApiService stuurt een GET-verzoek naar api.rawg.io/api/games.'],
                    ['bi-2-circle-fill', '#34d399', 'JSON verwerken',
                        'De RAWG API antwoordt met JSON. De ApiService parseert dit naar een bruikbare PHP-array.'],
                    ['bi-3-circle-fill', '#fbbf24', 'Importeren in database',
                        'Klik op "Importeer" om de game op te slaan in jouw MySQL-database via de repository.'],
                ];
                foreach ($steps as [$icon, $color, $title, $desc]): ?>
                    <div class="col-md-4">
                        <div class="gv-card" style="padding:1.5rem;text-align:center;">
                            <i class="bi <?= $icon ?>"
                               style="font-size:2rem;color:<?= $color ?>;display:block;margin-bottom:.75rem;"></i>
                            <h5 style="font-family:var(--font-display);font-weight:700;font-size:.95rem;margin-bottom:.4rem;">
                                <?= $title ?>
                            </h5>
                            <p style="font-size:.82rem;color:var(--text-secondary);margin:0;line-height:1.55;">
                                <?= $desc ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php elseif (empty($results)): ?>
            <!-- Geen resultaten -->
            <div class="gv-empty">
                <i class="bi bi-search"></i>
                <h3>Geen resultaten voor "<?= htmlspecialchars($query) ?>"</h3>
                <p>Probeer een andere zoekterm of controleer je spelling.</p>
            </div>

        <?php else: ?>
            <!-- Resultaten -->
            <p style="color:var(--text-muted);font-size:.85rem;margin-bottom:1.5rem;">
                <strong style="color:var(--text-secondary);"><?= count($results) ?></strong>
                resultaten voor "<strong style="color:var(--accent);"><?= htmlspecialchars($query) ?></strong>"
                via RAWG API
            </p>

            <div class="row g-4">
                <?php foreach ($results as $i => $result): ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3 fade-up"
                         style="animation-delay:<?= min($i * .04, .4) ?>s">
                        <div class="gv-api-card h-100 d-flex flex-column">

                            <!-- Cover -->
                            <div class="gv-api-card-img">
                                <?php if ($result['cover_url']): ?>
                                    <img src="<?= htmlspecialchars($result['cover_url']) ?>"
                                         alt="<?= htmlspecialchars($result['title']) ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="gv-card-placeholder" style="height:100%;">
                                        <i class="bi bi-controller"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Info -->
                            <div class="gv-api-card-body flex-grow-1 d-flex flex-column">
                                <?php if (!empty($result['genres'])): ?>
                                    <p class="gv-card-genre"><?= htmlspecialchars($result['genres'][0]) ?></p>
                                <?php endif; ?>

                                <h3 class="gv-api-card-title">
                                    <?= htmlspecialchars($result['title']) ?>
                                </h3>

                                <!-- Meta -->
                                <div class="d-flex justify-content-between align-items-center mt-auto"
                                     style="font-size:.78rem;color:var(--text-muted);padding-top:.6rem;
                                        border-top:1px solid var(--border);margin-top:.6rem;">
                                <span>
                                    <i class="bi bi-calendar3"></i>
                                    <?= $result['release_year'] ?? '–' ?>
                                </span>
                                    <?php if ($result['rating']): ?>
                                        <span style="color:var(--warning);font-weight:700;">
                                        <i class="bi bi-star-fill"></i>
                                        <?= number_format($result['rating'], 1) ?>
                                    </span>
                                    <?php endif; ?>
                                    <?php if ($result['metacritic']): ?>
                                        <span style="color:var(--success);font-weight:700;font-size:.72rem;">
                                        MC <?= $result['metacritic'] ?>
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Platform tags -->
                                <?php if (!empty($result['platforms'])): ?>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        <?php foreach (array_slice($result['platforms'], 0, 3) as $platform): ?>
                                            <span style="font-size:.66rem;background:rgba(255,255,255,.05);
                                                     border:1px solid var(--border);border-radius:5px;
                                                     padding:.15rem .45rem;color:var(--text-muted);">
                                            <?= htmlspecialchars($platform) ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Importeer knop -->
                                <form method="POST" class="mt-3">
                                    <input type="hidden" name="rawg_id" value="<?= (int)$result['rawg_id'] ?>">
                                    <button type="submit" class="gv-btn-primary gv-btn-sm w-100 justify-content-center">
                                        <i class="bi bi-cloud-arrow-down"></i> Importeer in collectie
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

<?php require_once '../../includes/footer.php'; ?>