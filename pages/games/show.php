<?php
session_start();

require_once '../../classes/Database.php';
require_once '../../classes/GameRepository.php';

$pdo        = Database::getInstance();
$repository = new GameRepository($pdo);

// Valideer & haal id op
$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$game = $id ? $repository->getById((int)$id) : null;

if (!$game) {
    $_SESSION['flash'][] = ['type' => 'error', 'message' => 'Game niet gevonden.'];
    header('Location: index.php');
    exit;
}

$pageTitle = htmlspecialchars($game->title) . ' – Vault';
require_once '../../includes/header.php';
?>

    <!-- ── Breadcrumb ──────────────────────────────────────────── -->
    <div style="background:var(--bg-surface);border-bottom:1px solid var(--border);padding:.75rem 0;">
        <div class="container">
            <p class="gv-breadcrumb mb-0">
                <a href="../../index.php">Home</a> /
                <a href="index.php">Games</a> /
                <?= htmlspecialchars($game->title) ?>
            </p>
        </div>
    </div>

    <!-- ── Detail hero ─────────────────────────────────────────── -->
    <div class="container gv-detail-hero">
        <div class="row g-5 align-items-start">

            <!-- Cover -->
            <div class="col-lg-5 fade-up">
                <div class="gv-detail-cover">
                    <?php if ($game->coverUrl): ?>
                        <img src="<?= htmlspecialchars($game->coverUrl) ?>"
                             alt="<?= htmlspecialchars($game->title) ?>">
                    <?php else: ?>
                        <div class="gv-card-placeholder" style="height:300px;">
                            <i class="bi bi-controller"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info -->
            <div class="col-lg-7 fade-up fade-up-1">

                <!-- Badges -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <?php if ($game->genreName): ?>
                        <span class="gv-badge"><i class="bi bi-tag"></i> <?= htmlspecialchars($game->genreName) ?></span>
                    <?php endif; ?>
                    <?php if ($game->platformName): ?>
                        <span class="gv-badge"><i class="bi bi-display"></i> <?= htmlspecialchars($game->platformName) ?></span>
                    <?php endif; ?>
                    <?php if ($game->releaseYear): ?>
                        <span class="gv-badge"><i class="bi bi-calendar3"></i> <?= $game->releaseYear ?></span>
                    <?php endif; ?>
                </div>

                <h1 style="font-size:clamp(1.8rem,4vw,3rem);font-weight:800;letter-spacing:-0.03em;margin-bottom:1.25rem;">
                    <?= htmlspecialchars($game->title) ?>
                </h1>

                <!-- Rating -->
                <?php if ($game->rating): ?>
                    <div class="d-flex align-items-center gap-3 mb-1.5" style="margin-bottom:1.5rem;">
                        <div>
                            <div class="gv-rating"><?= number_format($game->rating, 1) ?></div>
                            <div class="gv-rating-label">/ 10 gebruikersscore</div>
                        </div>
                        <div style="font-size:1.3rem;letter-spacing:2px;color:var(--warning);">
                            <?= $game->getStars() ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Beschrijving -->
                <?php if ($game->description): ?>
                    <p style="color:var(--text-secondary);line-height:1.75;font-size:1rem;margin-bottom:2rem;">
                        <?= nl2br(htmlspecialchars($game->description)) ?>
                    </p>
                <?php endif; ?>

                <!-- Acties -->
                <div class="d-flex flex-wrap gap-3">
                    <a href="edit.php?id=<?= $game->id ?>" class="gv-btn-primary">
                        <i class="bi bi-pencil"></i> Bewerken
                    </a>
                    <a href="delete.php?id=<?= $game->id ?>" class="gv-btn-danger">
                        <i class="bi bi-trash"></i> Verwijderen
                    </a>
                    <a href="index.php" class="gv-btn-outline">
                        <i class="bi bi-arrow-left"></i> Terug
                    </a>
                </div>

                <!-- Meta info -->
                <div class="d-flex flex-wrap gap-4 mt-4 pt-4" style="border-top:1px solid var(--border);">
                    <div>
                        <div style="font-size:.72rem;color:var(--text-muted);font-weight:600;letter-spacing:.05em;text-transform:uppercase;">Toegevoegd</div>
                        <div style="font-size:.9rem;color:var(--text-secondary);margin-top:.2rem;">
                            <?= date('d M Y', strtotime($game->createdAt)) ?>
                        </div>
                    </div>
                    <?php if ($game->rawgId): ?>
                        <div>
                            <div style="font-size:.72rem;color:var(--text-muted);font-weight:600;letter-spacing:.05em;text-transform:uppercase;">RAWG ID</div>
                            <div style="font-size:.9rem;color:var(--text-secondary);margin-top:.2rem;">#<?= $game->rawgId ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php require_once '../../includes/footer.php'; ?>