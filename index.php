<?php
session_start();

require_once 'classes/Database.php';
require_once 'classes/GameRepository.php';

$pdo        = Database::getInstance();
$repository = new GameRepository($pdo);

$recentGames = $repository->getAll('', 'g.created_at');
$recentGames = array_slice(array_reverse($recentGames), 0, 4);
$totalGames  = $repository->count();
$avgRating   = $repository->avgRating();

$pageTitle = 'Vault – Beheer jouw gamecollectie';
require_once 'includes/header.php';
?>

    <!Hero>
    <section class="gv-hero">
        <div class="container">
            <div class="row align-items-center">

                    <h1 class="gv-hero-title fade-up fade-up-1">
                        Jouw games,<br>
                        <span class="highlight">één overzicht.</span>
                    </h1>

                    <p class="gv-hero-sub fade-up fade-up-2">
                        Deze Vault helpt je je gamecollectie bij te houden.
                        Voeg games toe, pas ze aan en ontdek nieuwe titels
                        via de RAWG API.
                    </p>

                    <div class="gv-hero-actions fade-up fade-up-3">
                        <a href="pages/games/index.php" class="gv-btn-primary" style="font-size:1rem;padding:.75rem 1.75rem;">
                            <i class="bi bi-grid-3x3-gap"></i> Alle games
                        </a>
                        <a href="pages/games/create.php" class="gv-btn-outline" style="font-size:1rem;padding:.75rem 1.75rem;">
                            <i class="bi bi-plus-lg"></i> Game toevoegen
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="gv-stats fade-up fade-up-4">
                        <div>
                            <div class="gv-stat-value"><?= $totalGames ?></div>
                            <div class="gv-stat-label">Games in database</div>
                        </div>
                        <div>
                            <div class="gv-stat-value"><?= $avgRating > 0 ? number_format($avgRating, 1) : '–' ?></div>
                            <div class="gv-stat-label">Gemiddelde rating</div>
                        </div>
                        <div>
                            <div class="gv-stat-value" style="color:var(--accent)">RAWG</div>
                            <div class="gv-stat-label">API integratie</div>
                        </div>
                    </div>
                </div>

                <!decoratieve game-cards>
                <div class="col-lg-6 d-none d-lg-flex justify-content-center fade-up fade-up-2">
                    <div class="gv-hero-grid">
                        <?php
                        $heroGames = array_slice($recentGames, 0, 4);
                        // vul aan met lege placeholders als er minder dan 4 games zijn
                        while (count($heroGames) < 4) { $heroGames[] = null; }

                        foreach ($heroGames as $g): ?>
                            <div class="gv-hero-card-mini">
                                <?php if ($g && $g->coverUrl): ?>
                                    <img src="<?= htmlspecialchars($g->coverUrl) ?>"
                                         alt="<?= htmlspecialchars($g->title) ?>"
                                         loading="lazy">
                                <?php else: ?>
                                    <div class="gv-card-placeholder">
                                        <i class="bi bi-controller"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!Recente games>
    <section class="gv-section">
        <div class="container">

            <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-3">
                <div>
                    <p class="gv-section-label">Collectie</p>
                    <h2 class="gv-section-title mb-0">Recente toevoegingen</h2>
                </div>
                <a href="pages/games/index.php" class="gv-btn-outline gv-btn-sm">
                    Alle games <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <?php if (empty($recentGames)): ?>
                <div class="gv-empty">
                    <i class="bi bi-controller"></i>
                    <h3>Nog geen games</h3>
                    <p>Voeg je eerste game toe om te beginnen.</p>
                    <a href="pages/games/create.php" class="gv-btn-primary mt-3">
                        <i class="bi bi-plus-lg"></i> Game toevoegen
                    </a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($recentGames as $i => $game): ?>
                        <div class="col-sm-6 col-xl-3 fade-up" style="animation-delay:<?= $i * .07 ?>s">
                            <div class="gv-card">

                                <!Cover afbeelding>
                                <div class="gv-card-img-wrap">
                                    <?php if ($game->coverUrl): ?>
                                        <img src="<?= htmlspecialchars($game->coverUrl) ?>"
                                             alt="<?= htmlspecialchars($game->title) ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="gv-card-placeholder">
                                            <i class="bi bi-controller"></i>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($game->rating): ?>
                                        <div class="gv-card-badge">
                                            <i class="bi bi-star-fill"></i>
                                            <?= number_format($game->rating, 1) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!Body>
                                <div class="gv-card-body">
                                    <?php if ($game->genreName): ?>
                                        <p class="gv-card-genre"><?= htmlspecialchars($game->genreName) ?></p>
                                    <?php endif; ?>
                                    <h3 class="gv-card-title">
                                        <a href="pages/games/show.php?id=<?= $game->id ?>"
                                           style="color:inherit;text-decoration:none;">
                                            <?= htmlspecialchars($game->title) ?>
                                        </a>
                                    </h3>
                                    <p class="gv-card-desc">
                                        <?= htmlspecialchars($game->description ?: 'Geen beschrijving beschikbaar.') ?>
                                    </p>
                                    <div class="gv-card-meta">
                                        <span><?= $game->releaseYear ?? '–' ?></span>
                                        <span><?= htmlspecialchars($game->platformName ?? '–') ?></span>
                                    </div>
                                </div>

                                <!Acties>
                                <div class="gv-card-actions">
                                    <a href="pages/games/show.php?id=<?= $game->id ?>"
                                       class="gv-btn-outline gv-btn-sm flex-fill justify-content-center">
                                        <i class="bi bi-eye"></i> Bekijken
                                    </a>
                                    <a href="pages/games/edit.php?id=<?= $game->id ?>"
                                       class="gv-btn-primary gv-btn-sm">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>

    <!Feature blokken (over het project)>
    <section class="gv-section" style="padding-top:0">
        <div class="container">
            <div class="row g-4">

                <?php
                $features = [
                    ['bi-database',     'var(--accent)',   'Database & CRUD',    'Voeg, lees, bewerk en verwijder games met een MySQL-database en PDO.'],
                    ['bi-code-square',  '#34d399',         'OOP in PHP',         'Gestructureerde code via classes, repositories en een ApiService.'],
                    ['bi-cloud-arrow-down', '#fbbf24',     'RAWG API',           'Haal live gamedata op uit de RAWG-database en sla het op.'],
                    ['bi-phone',        '#f472b6',         'Responsive design',  'Bootstrap 5 zorgt dat de site op elk scherm perfect werkt.'],
                ];
                foreach ($features as $i => [$icon, $color, $title, $desc]): ?>
                    <div class="col-sm-6 col-lg-3 fade-up" style="animation-delay:<?= $i*.07 ?>s">
                        <div class="gv-card" style="padding:1.75rem;">
                            <div style="width:48px;height:48px;background:<?= $color ?>18;border:1px solid <?= $color ?>33;
                                border-radius:12px;display:flex;align-items:center;justify-content:center;
                                font-size:1.3rem;color:<?= $color ?>;margin-bottom:1.25rem;">
                                <i class="bi <?= $icon ?>"></i>
                            </div>
                            <h4 style="font-size:1rem;font-weight:700;margin-bottom:.5rem;"><?= $title ?></h4>
                            <p style="font-size:.85rem;color:var(--text-secondary);margin:0;line-height:1.55;"><?= $desc ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>