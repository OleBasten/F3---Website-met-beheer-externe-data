<?php
session_start();

require_once '../../classes/Database.php';
require_once '../../classes/GameRepository.php';

$pdo        = Database::getInstance();
$repository = new GameRepository($pdo);

$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$game = $id ? $repository->getById((int)$id) : null;

if (!$game) {
    $_SESSION['flash'][] = ['type' => 'error', 'message' => 'Game niet gevonden.'];
    header('Location: index.php');
    exit;
}

//Verwerk POST (bevestiging)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $title = $game->title;
    $repository->delete((int)$id);

    $_SESSION['flash'][] = [
        'type'    => 'success',
        'message' => "'{$title}' is verwijderd."
    ];
    header('Location: index.php');
    exit;
}

$pageTitle = 'Verwijderen: ' . htmlspecialchars($game->title) . ' – Vault';
require_once '../../includes/header.php';
?>

    <div class="container" style="max-width:600px;padding:4rem 1rem;">

        <p class="gv-breadcrumb">
            <a href="../../index.php">Home</a> /
            <a href="index.php">Games</a> /
            <a href="show.php?id=<?= $game->id ?>"><?= htmlspecialchars($game->title) ?></a> /
            Verwijderen
        </p>

        <div class="gv-danger-card mt-3 fade-up">
            <div class="gv-danger-icon">
                <i class="bi bi-trash"></i>
            </div>

            <h2 style="font-size:1.5rem;font-weight:800;margin-bottom:.75rem;">
                Game verwijderen?
            </h2>

            <p style="color:var(--text-secondary);margin-bottom:.5rem;">
                Je staat op het punt om de volgende game te verwijderen:
            </p>

            <p style="font-family:var(--font-display);font-size:1.15rem;font-weight:700;
                  color:var(--text-primary);margin-bottom:1.75rem;">
                "<?= htmlspecialchars($game->title) ?>"
            </p>

            <p style="color:var(--danger);font-size:.85rem;margin-bottom:2rem;">
                <i class="bi bi-exclamation-triangle"></i>
                Deze actie kan <strong>niet</strong> ongedaan worden gemaakt.
            </p>

            <div class="d-flex gap-3 justify-content-center flex-wrap">
                <form method="POST">
                    <input type="hidden" name="id"      value="<?= $game->id ?>">
                    <input type="hidden" name="confirm" value="1">
                    <button type="submit" class="gv-btn-danger" style="font-size:.95rem;padding:.65rem 1.5rem;">
                        <i class="bi bi-trash"></i> Ja, verwijder definitief
                    </button>
                </form>

                <a href="show.php?id=<?= $game->id ?>" class="gv-btn-outline" style="font-size:.95rem;padding:.65rem 1.5rem;">
                    <i class="bi bi-arrow-left"></i> Annuleren
                </a>
            </div>
        </div>

    </div>

<?php require_once '../../includes/footer.php'; ?>