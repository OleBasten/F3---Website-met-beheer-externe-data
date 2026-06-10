<?php
/**
 * search.php – RAWG API zoekpagina met inhoudsfilter
 *
 * Filter-opties (opgeslagen in sessie):
 *   'filter' → verberg 18+ inhoud volledig (standaard, veiligst)
 *   'blur'   → toon 18+ resultaten met wazig beeld + klikbare onthulling
 *   'off'    → geen filter (met waarschuwingsdialoog)
 *
 * ESRB-drempel: id >= 4 (Mature 17+ of Adults Only 18+)
 */

require_once '../../includes/security.php';
security_configure_session();
session_start();

require_once '../../classes/Database.php';
require_once '../../classes/GameRepository.php';
require_once '../../classes/ApiService.php';

$pdo        = Database::getInstance();
$repository = new GameRepository($pdo);
$apiService = new ApiService();
$genres     = $repository->getAllGenres();

// ── Inhoudsfilter instelling ───────────────────────────────────────────────
// Valideer de gewenste filterwaarde
$allowedFilters = ['filter', 'blur', 'off'];

if (isset($_GET['content_filter']) && in_array($_GET['content_filter'], $allowedFilters, true)) {
    $_SESSION['content_filter'] = $_GET['content_filter'];
}

// Standaard is 'filter' (veiligst – geen 18+ zichtbaar)
$contentFilter = $_SESSION['content_filter'] ?? 'filter';

// ── Zoekquery ─────────────────────────────────────────────────────────────
$query   = htmlspecialchars(substr(trim($_GET['q'] ?? ''), 0, 100), ENT_QUOTES, 'UTF-8');
$results = [];
$error   = null;

if (mb_strlen($query) >= 2) {
    $results = $apiService->searchGames($query, 20); // meer ophalen zodat filter genoeg overhoudt
    if ($results === null) {
        $error   = 'Kon geen verbinding maken met de RAWG API.';
        $results = [];
    }
}

// ── Importeer via POST (CSRF-beschermd) ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rawg_id'])) {
    csrf_validate('search.php' . ($query ? '?q=' . urlencode($query) : ''));

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
            'message' => "'{$details['title']}' is geïmporteerd vanuit de RAWG API!",
        ];
        header('Location: ../games/show.php?id=' . (int)$newId);
        exit;
    }

    $_SESSION['flash'][] = [
        'type'    => 'error',
        'message' => 'Kon de gamedetails niet ophalen. Probeer het opnieuw.',
    ];
}

// ── Tel hoeveel resultaten gefilterd worden ───────────────────────────────
$matureCount = 0;
foreach ($results as $r) {
    if (ApiService::isMatureContent($r['esrb_rating'] ?? null)) $matureCount++;
}

$pageTitle       = 'API Zoeken – Vault';
$pageDescription = 'Zoek en importeer games vanuit de RAWG Video Games Database API.';
require_once '../../includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════════════
     WAARSCHUWINGSDIALOOG voor 'filter uitschakelen'
     Wordt getoond via JS wanneer gebruiker op "Uit" klikt
═══════════════════════════════════════════════════════════════ -->
<div id="filterWarningOverlay" role="dialog" aria-modal="true"
     aria-labelledby="filterWarnTitle" aria-describedby="filterWarnDesc"
     style="display:none;position:fixed;inset:0;z-index:9999;
            background:rgba(0,0,0,.75);backdrop-filter:blur(6px);
            align-items:center;justify-content:center;padding:1rem;">
    <div style="background:var(--card-bg);border:1px solid #ef4444;border-radius:18px;
                max-width:420px;width:100%;padding:2rem;position:relative;">

        <div style="width:52px;height:52px;background:#ef444422;border:1px solid #ef4444;
                    border-radius:50%;display:flex;align-items:center;justify-content:center;
                    margin:0 auto 1.25rem;font-size:1.5rem;" aria-hidden="true">
            ⚠️
        </div>

        <h2 id="filterWarnTitle"
            style="text-align:center;font-family:var(--font-display);font-size:1.2rem;
                   font-weight:800;color:#ef4444;margin-bottom:.75rem;">
            Filter uitschakelen?
        </h2>

        <p id="filterWarnDesc"
           style="text-align:center;color:var(--text-secondary);font-size:.9rem;
                  line-height:1.6;margin-bottom:1.5rem;">
            Wanneer je het filter uitschakelt kunnen resultaten worden
            getoond die <strong>geschikt zijn voor personen van 18 jaar en ouder</strong>
            (Mature / Adults Only ESRB-rating).<br><br>
            Bevestig dat je <strong>18 jaar of ouder</strong> bent om door te gaan.
        </p>

        <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
            <button id="filterWarnCancel"
                    style="flex:1;padding:.65rem 1rem;border-radius:10px;border:1px solid var(--border);
                           background:transparent;color:var(--text-secondary);cursor:pointer;
                           font-family:var(--font-body);font-size:.9rem;font-weight:600;">
                <i class="bi bi-x" aria-hidden="true"></i> Annuleren
            </button>
            <a id="filterWarnConfirm" href="#"
               style="flex:1;padding:.65rem 1rem;border-radius:10px;border:none;
                      background:#ef4444;color:#fff;cursor:pointer;text-align:center;
                      font-family:var(--font-body);font-size:.9rem;font-weight:700;
                      text-decoration:none;display:block;">
                Ik ben 18+ – Filter uitschakelen
            </a>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     PAGE HEADER
═══════════════════════════════════════════════════════════════ -->
<div class="gv-page-header">
    <div class="container">

        <div class="gv-page-header-inner">
            <div>
                <p class="gv-breadcrumb">
                    <a href="../../index.php">Home</a> /
                    <span aria-current="page">API Zoeken</span>
                </p>
                <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;margin:.25rem 0 0;">
                    <i class="bi bi-cloud-arrow-down text-accent" aria-hidden="true"></i>
                    RAWG API Zoeken
                </h1>
                <p style="color:var(--text-secondary);font-size:.88rem;margin:.4rem 0 0;">
                    Data afkomstig van
                    <a href="https://rawg.io" target="_blank" rel="noopener noreferrer"
                       style="color:var(--accent);font-weight:600;">RAWG.io</a>
                    – de grootste open speldata-database
                </p>
            </div>
            <a href="../games/index.php" class="gv-btn-outline gv-btn-sm">
                <i class="bi bi-collection" aria-hidden="true"></i> Mijn collectie
            </a>
        </div>

        <!-- ─── Zoekformulier ─────────────────────────────────────── -->
        <search>
            <form method="GET" class="mt-4" role="search" aria-label="Zoek games via RAWG API">
                <div class="d-flex flex-wrap gap-3 align-items-end">

                    <div class="flex-grow-1" style="max-width:480px;">
                        <label class="form-label" for="apiSearchInput">
                            Zoek een game via de RAWG API
                        </label>
                        <div class="gv-search-wrap">
                            <i class="bi bi-search" aria-hidden="true"></i>
                            <input type="search"
                                   name="q"
                                   id="apiSearchInput"
                                   class="form-control"
                                   placeholder="bijv. Elden Ring, Minecraft, GTA…"
                                   value="<?= htmlspecialchars($query) ?>"
                                   minlength="2"
                                   maxlength="100"
                                   autocomplete="off"
                                   spellcheck="false"
                                   aria-label="Zoekterm voor RAWG API"
                                   aria-describedby="searchHint"
                                   autofocus>
                        </div>
                        <div id="searchHint" class="form-text" style="color:var(--text-muted);font-size:.78rem;">
                            Minimaal 2 tekens. Druk op Enter of klik Zoeken.
                        </div>
                    </div>

                    <button type="submit" class="gv-btn-primary"
                            style="padding:.65rem 1.5rem;"
                            aria-label="Zoeken in RAWG API">
                        <i class="bi bi-search" aria-hidden="true"></i> Zoeken
                    </button>

                    <?php if ($query): ?>
                        <a href="search.php" class="gv-btn-outline gv-btn-sm"
                           style="align-self:flex-end;"
                           aria-label="Zoekopdracht wissen">
                            <i class="bi bi-x" aria-hidden="true"></i> Wis
                        </a>
                    <?php endif; ?>

                </div>
            </form>
        </search>

        <!-- ─── Inhoudsfilter ─────────────────────────────────────── -->
        <div class="gv-filter-bar mt-3" role="group" aria-label="Inhoudsfilter voor 18+ inhoud">
            <span class="gv-filter-label">
                <i class="bi bi-shield" aria-hidden="true"></i>
                Inhoudsfilter:
            </span>

            <?php
            $filterOptions = [
                'filter' => ['bi-shield-fill-check',  '#22c55e', 'Filter',
                             'Verberg 18+ inhoud volledig (standaard)'],
                'blur'   => ['bi-eye-slash',           '#f59e0b', 'Vervaag',
                             'Toon 18+ inhoud wazig, klik om te onthullen'],
                'off'    => ['bi-shield-slash',        '#ef4444', 'Uit',
                             'Geen filter – alleen voor 18+'],
            ];
            foreach ($filterOptions as $key => [$icon, $color, $label, $tip]): ?>
                <?php if ($key === 'off'): ?>
                    <!-- "Uit" → toon eerst waarschuwingsdialoog via JS -->
                    <button type="button"
                            class="gv-filter-btn <?= $contentFilter === $key ? 'active' : '' ?>"
                            data-filter="off"
                            data-target="<?= htmlspecialchars(
                                '?content_filter=off' . ($query ? '&q=' . urlencode($query) : '')
                            ) ?>"
                            style="<?= $contentFilter === $key ? "--btn-color:{$color}" : '' ?>"
                            aria-pressed="<?= $contentFilter === $key ? 'true' : 'false' ?>"
                            aria-describedby="tip-<?= $key ?>"
                            title="<?= htmlspecialchars($tip) ?>">
                        <i class="bi <?= $icon ?>" aria-hidden="true" style="color:<?= $color ?>"></i>
                        <?= $label ?>
                    </button>
                <?php else: ?>
                    <a href="?content_filter=<?= $key ?><?= $query ? '&q=' . urlencode($query) : '' ?>"
                       class="gv-filter-btn <?= $contentFilter === $key ? 'active' : '' ?>"
                       style="<?= $contentFilter === $key ? "--btn-color:{$color}" : '' ?>"
                       aria-pressed="<?= $contentFilter === $key ? 'true' : 'false' ?>"
                       aria-describedby="tip-<?= $key ?>"
                       title="<?= htmlspecialchars($tip) ?>">
                        <i class="bi <?= $icon ?>" aria-hidden="true" style="color:<?= $color ?>"></i>
                        <?= $label ?>
                    </a>
                <?php endif; ?>
                <!-- Tooltip-tekst voor schermlezers -->
                <span id="tip-<?= $key ?>" class="visually-hidden"><?= htmlspecialchars($tip) ?></span>
            <?php endforeach; ?>

            <!-- Teller van gefilterde resultaten -->
            <?php if ($matureCount > 0 && $contentFilter === 'filter'): ?>
                <span class="gv-filter-count" role="status" aria-live="polite">
                    <i class="bi bi-eye-slash" aria-hidden="true"></i>
                    <?= $matureCount ?> resultaat<?= $matureCount > 1 ? 'en' : '' ?> verborgen
                </span>
            <?php elseif ($matureCount > 0 && $contentFilter === 'blur'): ?>
                <span class="gv-filter-count" style="color:#f59e0b;" role="status" aria-live="polite">
                    <i class="bi bi-eye-slash" aria-hidden="true"></i>
                    <?= $matureCount ?> resultaat<?= $matureCount > 1 ? 'en' : '' ?> vervaagd
                </span>
            <?php endif; ?>
        </div>

        <!-- Banner: filter staat uit -->
        <?php if ($contentFilter === 'off'): ?>
            <div role="alert"
                 style="margin-top:.75rem;padding:.65rem 1rem;border-radius:10px;
                        background:#ef444415;border:1px solid #ef444455;
                        font-size:.83rem;color:#fca5a5;display:flex;align-items:center;gap:.5rem;">
                <i class="bi bi-shield-slash" aria-hidden="true" style="font-size:1rem;"></i>
                Inhoudsfilter staat <strong>uitgeschakeld</strong>.
                Resultaten kunnen inhoud bevatten voor 18 jaar en ouder.
                <a href="?content_filter=filter<?= $query ? '&q=' . urlencode($query) : '' ?>"
                   style="color:#ef4444;font-weight:700;margin-left:auto;white-space:nowrap;">
                    Filter inschakelen →
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     RESULTATEN
═══════════════════════════════════════════════════════════════ -->
<div class="container" style="padding-bottom:5rem;"
     aria-live="polite" aria-atomic="false"
     role="region" aria-label="Zoekresultaten">

    <?php if ($error): ?>
        <!-- API-fout -->
        <div class="gv-empty" role="alert">
            <i class="bi bi-wifi-off" style="color:var(--danger);" aria-hidden="true"></i>
            <h2>API niet bereikbaar</h2>
            <p style="max-width:420px;"><?= htmlspecialchars($error) ?></p>
        </div>

    <?php elseif (mb_strlen($query) === 1): ?>
        <div class="gv-empty" role="status">
            <i class="bi bi-keyboard" aria-hidden="true" style="color:var(--accent);"></i>
            <h2>Typ iets meer</h2>
            <p>Voer minimaal 2 tekens in om te zoeken.</p>
        </div>

    <?php elseif ($query === ''): ?>
        <div class="gv-empty" style="padding-bottom:1rem;" role="status">
            <i class="bi bi-controller" style="color:var(--accent);" aria-hidden="true"></i>
            <h2>Zoek games in de RAWG-database</h2>
            <p>Typ een gamenaam hierboven om live resultaten te zien.</p>
        </div>

        <!-- Hoe werkt de filter – uitleg -->
        <div class="row g-3 mt-2" style="max-width:860px;margin:0 auto;">
            <?php
            $filterInfo = [
                ['bi-shield-fill-check','#22c55e','Filter (standaard)',
                    'Alle 18+-resultaten worden volledig verborgen. De veiligste instelling voor schoolomgevingen.'],
                ['bi-eye-slash','#f59e0b','Vervaag',
                    'Coversafbeeldingen van 18+-spellen worden wazig weergegeven. Klik om te onthullen.'],
                ['bi-shield-slash','#ef4444','Uit',
                    'Geen filter actief. Enkel voor meerderjarigen. Vereist leeftijdsbevestiging (18+).'],
            ];
            foreach ($filterInfo as [$icon, $color, $title, $desc]): ?>
                <div class="col-md-4">
                    <div class="gv-card" style="padding:1.5rem;text-align:center;">
                        <i class="bi <?= $icon ?>"
                           style="font-size:2rem;color:<?= $color ?>;display:block;margin-bottom:.75rem;"
                           aria-hidden="true"></i>
                        <h3 style="font-family:var(--font-display);font-weight:700;font-size:.95rem;margin-bottom:.4rem;">
                            <?= htmlspecialchars($title) ?>
                        </h3>
                        <p style="font-size:.82rem;color:var(--text-secondary);margin:0;line-height:1.55;">
                            <?= htmlspecialchars($desc) ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif (empty($results) || ($contentFilter === 'filter' && count($results) === $matureCount)): ?>
        <div class="gv-empty" role="status">
            <i class="bi bi-search" aria-hidden="true"></i>
            <h2>Geen resultaten
                <?php if ($contentFilter === 'filter' && $matureCount > 0): ?>
                    <span style="font-size:.85rem;color:var(--text-muted);font-weight:400;display:block;margin-top:.25rem;">
                        (<?= $matureCount ?> resultaat<?= $matureCount > 1 ? 'en' : '' ?> verborgen door inhoudsfilter)
                    </span>
                <?php endif; ?>
            </h2>
            <p>Probeer een andere zoekterm of pas het inhoudsfilter aan.</p>
        </div>

    <?php else: ?>

        <p style="color:var(--text-muted);font-size:.85rem;margin-bottom:1.5rem;" role="status">
            <?php
            $shownCount = ($contentFilter === 'filter')
                ? count($results) - $matureCount
                : count($results);
            ?>
            <strong style="color:var(--text-secondary);"><?= $shownCount ?></strong>
            resultaten voor "<strong style="color:var(--accent);"><?= htmlspecialchars($query) ?></strong>"
            via <a href="https://rawg.io" target="_blank" rel="noopener noreferrer"
                   style="color:var(--accent);">RAWG API</a>
        </p>

        <div class="row g-4" role="list" aria-label="RAWG zoekresultaten">
            <?php foreach ($results as $i => $result):
                $isMature = ApiService::isMatureContent($result['esrb_rating'] ?? null);
                $esrbName = $result['esrb_rating']['name'] ?? null;

                // Filter-modus: sla 18+ volledig over
                if ($isMature && $contentFilter === 'filter') continue;
            ?>

                <div class="col-sm-6 col-lg-4 col-xl-3 fade-up"
                     style="animation-delay:<?= min($i * .04, .4) ?>s"
                     role="listitem">
                    <article class="gv-api-card h-100 d-flex flex-column
                                    <?= $isMature ? 'mature-card' : '' ?>"
                             aria-label="<?= htmlspecialchars($result['title']) ?>
                                         <?= $isMature ? ' – Mature (18+)' : '' ?>">

                        <!-- Cover afbeelding -->
                        <div class="gv-api-card-img <?= ($isMature && $contentFilter === 'blur') ? 'mature-blur-wrap' : '' ?>">
                            <?php if ($result['cover_url']): ?>
                                <img src="<?= htmlspecialchars($result['cover_url']) ?>"
                                     alt="<?= htmlspecialchars($result['title']) ?> cover<?= $isMature ? ' (18+ inhoud)' : '' ?>"
                                     class="<?= ($isMature && $contentFilter === 'blur') ? 'mature-img' : '' ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div class="gv-card-placeholder" style="height:100%;" aria-hidden="true">
                                    <i class="bi bi-controller"></i>
                                </div>
                            <?php endif; ?>

                            <!-- Blur-overlay: klik om te onthullen -->
                            <?php if ($isMature && $contentFilter === 'blur'): ?>
                                <button class="blur-reveal-btn"
                                        aria-label="<?= htmlspecialchars($result['title']) ?> onthullen – Mature (18+) inhoud"
                                        title="Klik om te onthullen (18+ inhoud)">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                    <span>18+</span>
                                    <small>Klik om te onthullen</small>
                                </button>
                            <?php endif; ?>

                            <!-- ESRB-badge -->
                            <?php if ($esrbName): ?>
                                <div class="gv-esrb-badge <?= $isMature ? 'mature' : 'safe' ?>"
                                     aria-label="ESRB: <?= htmlspecialchars($esrbName) ?>">
                                    <?= htmlspecialchars($esrbName) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Kaartinhoud -->
                        <div class="gv-api-card-body flex-grow-1 d-flex flex-column">
                            <?php if (!empty($result['genres'])): ?>
                                <p class="gv-card-genre"><?= htmlspecialchars($result['genres'][0]) ?></p>
                            <?php endif; ?>

                            <h3 class="gv-api-card-title">
                                <?= htmlspecialchars($result['title']) ?>
                            </h3>

                            <div class="d-flex justify-content-between align-items-center mt-auto"
                                 style="font-size:.78rem;color:var(--text-muted);padding-top:.6rem;
                                        border-top:1px solid var(--border);margin-top:.6rem;">
                                <span>
                                    <i class="bi bi-calendar3" aria-hidden="true"></i>
                                    <span class="visually-hidden">Uitgebracht: </span>
                                    <?= htmlspecialchars((string)($result['release_year'] ?? '–')) ?>
                                </span>
                                <?php if ($result['rating']): ?>
                                    <span style="color:var(--warning);font-weight:700;"
                                          aria-label="Rating: <?= number_format($result['rating'], 1) ?>">
                                        <i class="bi bi-star-fill" aria-hidden="true"></i>
                                        <?= number_format($result['rating'], 1) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($result['metacritic']): ?>
                                    <span style="color:var(--success);font-weight:700;font-size:.72rem;"
                                          aria-label="Metacritic: <?= (int)$result['metacritic'] ?>">
                                        MC <?= (int)$result['metacritic'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($result['platforms'])): ?>
                                <div class="d-flex flex-wrap gap-1 mt-2" aria-label="Platforms">
                                    <?php foreach (array_slice($result['platforms'], 0, 3) as $platform): ?>
                                        <span style="font-size:.66rem;background:rgba(255,255,255,.05);
                                                     border:1px solid var(--border);border-radius:5px;
                                                     padding:.15rem .45rem;color:var(--text-muted);">
                                            <?= htmlspecialchars($platform) ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="mt-3">
                                <?= csrf_field() ?>
                                <input type="hidden" name="rawg_id" value="<?= (int)$result['rawg_id'] ?>">
                                <button type="submit"
                                        class="gv-btn-primary gv-btn-sm w-100 justify-content-center"
                                        aria-label="<?= htmlspecialchars($result['title']) ?> importeren in collectie">
                                    <i class="bi bi-cloud-arrow-down" aria-hidden="true"></i>
                                    Importeer in collectie
                                </button>
                            </form>
                        </div>

                    </article>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT – dialoog + blur onthullen
═══════════════════════════════════════════════════════════════ -->
<script>
(function () {
    'use strict';

    // ── Waarschuwingsdialoog voor "filter uitschakelen" ─────────
    const overlay     = document.getElementById('filterWarningOverlay');
    const cancelBtn   = document.getElementById('filterWarnCancel');
    const confirmLink = document.getElementById('filterWarnConfirm');

    document.querySelectorAll('[data-filter="off"]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            // Als filter al 'off' is, geen dialoog nodig
            if (btn.classList.contains('active')) return;

            // Stel de doellink in op de bevestigingsknop
            confirmLink.href = btn.dataset.target || '?content_filter=off';

            // Toon overlay
            overlay.style.display = 'flex';
            cancelBtn.focus();
        });
    });

    // Annuleren
    cancelBtn.addEventListener('click', function () {
        overlay.style.display = 'none';
    });

    // Sluit bij klik buiten het dialoogvenster
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) overlay.style.display = 'none';
    });

    // Sluit met Escape-toets (toegankelijkheid)
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.style.display !== 'none') {
            overlay.style.display = 'none';
        }
    });

    // ── Blur onthullen (klik op overlay-knop) ──────────────────
    document.querySelectorAll('.blur-reveal-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const wrap = btn.closest('.mature-blur-wrap');
            if (!wrap) return;

            // Verwijder blur van afbeelding
            const img = wrap.querySelector('.mature-img');
            if (img) {
                img.classList.remove('mature-img');
                img.style.filter = 'none';
            }

            // Verberg de overlay-knop
            btn.style.display = 'none';
            wrap.classList.remove('mature-blur-wrap');
        });
    });
})();
</script>

<?php require_once '../../includes/footer.php'; ?>
