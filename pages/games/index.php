<?php

session_start();

require_once '../../classes/Database.php';
require_once '../../classes/GameRepository.php';

$pdo        = Database::getInstance();
$repository = new GameRepository($pdo);

// Zoek- en sorteerparameters – Week 6: SQL LIKE + ORDER BY
$search  = trim($_GET['search']  ?? '');
$orderBy = $_GET['order']  ?? 'g.title';

$games    = $repository->getAll($search, $orderBy);
$genres   = $repository->getAllGenres();
$platforms= $repository->getAllPlatforms();

$pageTitle = 'Games – Vault';
require_once '../../includes/header.php';
?>

    <!-- ── Page header ─────────────────────────────────────────── -->
    <div class="gv-page-header">
        <div class="container">
            <div class="gv-page-header-inner">

                <div>
                    <p class="gv-breadcrumb">
                        <a href="../../index.php">Home</a> / Games
                    </p>
                    <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;margin:0;">
                        Gamecollectie
                        <span style="font-size:1rem;font-weight:500;color:var(--text-muted);margin-left:.5rem;">
                        (<?= count($games) ?> games)
                    </span>
                    </h1>
                </div>

                <a href="create.php" class="gv-btn-primary">
                    <i class="bi bi-plus-lg"></i> Game toevoegen
                </a>
            </div>

            <!-- Zoekbalk + sortering -->
            <form method="GET" class="mt-4 d-flex flex-wrap gap-3 align-items-center">
                <div class="gv-search-wrap flex-grow-1" style="max-width:380px;">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" id="searchInput"
                           class="form-control" placeholder="Zoek op naam of genre…"
                           value="<?= htmlspecialchars($search) ?>">
                </div>

                <select name="order" class="form-select" style="width:auto;"
                        onchange="this.form.submit()">
                    <option value="g.title"        <?= $orderBy === 'g.title'        ? 'selected' : '' ?>>Naam A–Z</option>
                    <option value="g.rating"       <?= $orderBy === 'g.rating'       ? 'selected' : '' ?>>Rating</option>
                    <option value="g.release_year" <?= $orderBy === 'g.release_year' ? 'selected' : '' ?>>Jaar</option>
                    <option value="genres.name"    <?= $orderBy === 'genres.name'    ? 'selected' : '' ?>>Genre</option>
                </select>

                <?php if ($search): ?>
                    <a href="index.php" class="gv-btn-outline gv-btn-sm">
                        <i class="bi bi-x"></i> Wis filter
                    </a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- ── Games grid ──────────────────────────────────────────── -->
    <div class="container" style="padding-bottom:5rem;">

        <?php if (empty($games)): ?>
            <div class="gv-empty">
                <i class="bi bi-search"></i>
                <h3>Geen games gevonden</h3>
                <p>Probeer een andere zoekterm of voeg een nieuwe game toe.</p>
                <a href="create.php" class="gv-btn-primary mt-3">
                    <i class="bi bi-plus-lg"></i> Game toevoegen
                </a>
            </div>

        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($games as $i => $game): ?>
                    <div class="col-sm-6 col-lg-4 col-xl-3 fade-up"
                         style="animation-delay:<?= min($i * .05, .4) ?>s">
                        <div class="gv-card">

                            <!-- Cover -->
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

                            <!-- Info -->
                            <div class="gv-card-body">
                                <?php if ($game->genreName): ?>
                                    <p class="gv-card-genre"><?= htmlspecialchars($game->genreName) ?></p>
                                <?php endif; ?>
                                <h2 class="gv-card-title" style="font-size:1rem;">
                                    <?= htmlspecialchars($game->title) ?>
                                </h2>
                                <p class="gv-card-desc">
                                    <?= htmlspecialchars($game->description ?: '–') ?>
                                </p>
                                <div class="gv-card-meta">
                                    <span><i class="bi bi-calendar3"></i> <?= $game->releaseYear ?? '–' ?></span>
                                    <span><?= htmlspecialchars($game->platformName ?? '–') ?></span>
                                </div>
                            </div>

                            <!-- Acties -->
                            <div class="gv-card-actions" style="flex-wrap:wrap;">
                                <a href="show.php?id=<?= $game->id ?>"
                                   class="gv-btn-outline gv-btn-sm flex-fill justify-content-center">
                                    <i class="bi bi-eye"></i> Bekijk
                                </a>
                                <a href="edit.php?id=<?= $game->id ?>"
                                   class="gv-btn-primary gv-btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="delete.php?id=<?= $game->id ?>"
                                   class="gv-btn-danger gv-btn-sm">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

<?php require_once '../../includes/footer.php'; ?>