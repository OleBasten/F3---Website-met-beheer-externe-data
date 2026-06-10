<?php
require_once '../../includes/security.php';
security_configure_session();
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

$genres    = $repository->getAllGenres();
$platforms = $repository->getAllPlatforms();
$errors    = [];

//Verwerk POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($_POST['title']        ?? '');
    $description = trim($_POST['description']  ?? '');
    $genreId     = (int) ($_POST['genre_id']    ?? 0);
    $platformId  = (int) ($_POST['platform_id'] ?? 0);
    $releaseYear = (int) ($_POST['release_year']?? 0);
    $rating      = trim($_POST['rating']        ?? '');
    $coverUrl    = trim($_POST['cover_url']     ?? '');

    // Week 2 – Validatie
    if ($title === '') {
        $errors['title'] = 'Titel is verplicht.';
    } elseif (strlen($title) > 255) {
        $errors['title'] = 'Titel mag maximaal 255 tekens zijn.';
    }

    if ($releaseYear && ($releaseYear < 1970 || $releaseYear > (int)date('Y') + 2)) {
        $errors['release_year'] = 'Voer een geldig jaar in.';
    }

    if ($rating !== '' && (!is_numeric($rating) || $rating < 0 || $rating > 10)) {
        $errors['rating'] = 'Rating moet tussen 0 en 10 liggen.';
    }

    if ($coverUrl && !filter_var($coverUrl, FILTER_VALIDATE_URL)) {
        $errors['cover_url'] = 'Voer een geldige URL in.';
    }

    if (empty($errors)) {
        $repository->update((int)$id, [
            'title'        => $title,
            'description'  => $description,
            'genre_id'     => $genreId    ?: null,
            'platform_id'  => $platformId ?: null,
            'release_year' => $releaseYear ?: null,
            'rating'       => $rating !== '' ? $rating : null,
            'cover_url'    => $coverUrl   ?: null,
        ]);

        $_SESSION['flash'][] = [
            'type'    => 'success',
            'message' => "'{$title}' is bijgewerkt."
        ];
        header("Location: show.php?id={$id}");
        exit;
    }

    //Bij fouten: gebruik POST-waarden als invulwaarden
    $game->title        = $_POST['title']        ?? $game->title;
    $game->description  = $_POST['description']  ?? $game->description;
    $game->genreId      = (int)($_POST['genre_id']    ?? 0) ?: null;
    $game->platformId   = (int)($_POST['platform_id'] ?? 0) ?: null;
    $game->releaseYear  = (int)($_POST['release_year']?? 0) ?: null;
    $game->rating       = is_numeric($_POST['rating'] ?? '') ? (float)$_POST['rating'] : null;
    $game->coverUrl     = $_POST['cover_url']    ?? $game->coverUrl;
}

$pageTitle = 'Bewerken: ' . htmlspecialchars($game->title) . ' – Vault';
require_once '../../includes/header.php';
?>

    <div class="container gv-form-page">

        <div class="gv-form-header">
            <p class="gv-breadcrumb">
                <a href="../../index.php">Home</a> /
                <a href="index.php">Games</a> /
                <a href="show.php?id=<?= $game->id ?>"><?= htmlspecialchars($game->title) ?></a> /
                Bewerken
            </p>
            <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;margin-top:.5rem;">
                <i class="bi bi-pencil text-accent"></i> Game bewerken
            </h1>
        </div>

        <div class="row g-4">

            <div class="col-lg-8">
                <div class="gv-form-card fade-up">
                    <form method="POST" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= $game->id ?>">

                        <!Titel>
                        <div class="mb-4">
                            <label for="title" class="form-label">Titel *</label>
                            <input type="text" id="title" name="title"
                                   class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($game->title) ?>"
                                   maxlength="255" required>
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback"><?= $errors['title'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!Beschrijving>
                        <div class="mb-4">
                            <label for="description" class="form-label">Beschrijving</label>
                            <textarea id="description" name="description"
                                      class="form-control" rows="4"><?= htmlspecialchars($game->description) ?></textarea>
                        </div>

                        <!Genre + Platform>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label for="genre_id" class="form-label">Genre</label>
                                <select id="genre_id" name="genre_id" class="form-select">
                                    <option value="">– Geen –</option>
                                    <?php foreach ($genres as $g): ?>
                                        <option value="<?= $g['id'] ?>"
                                            <?= $game->genreId == $g['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($g['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="platform_id" class="form-label">Platform</label>
                                <select id="platform_id" name="platform_id" class="form-select">
                                    <option value="">– Geen –</option>
                                    <?php foreach ($platforms as $p): ?>
                                        <option value="<?= $p['id'] ?>"
                                            <?= $game->platformId == $p['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!Jaar + Rating>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label for="release_year" class="form-label">Jaar van uitgifte</label>
                                <input type="number" id="release_year" name="release_year"
                                       class="form-control <?= isset($errors['release_year']) ? 'is-invalid' : '' ?>"
                                       value="<?= $game->releaseYear ?>"
                                       min="1970" max="<?= date('Y') + 2 ?>">
                                <?php if (isset($errors['release_year'])): ?>
                                    <div class="invalid-feedback"><?= $errors['release_year'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-6">
                                <label for="rating" class="form-label">
                                    Rating (0–10)
                                    <span id="ratingDisplay" style="color:var(--warning);font-weight:700;">
                                    <?= $game->rating ? number_format($game->rating, 1) : '–' ?>
                                </span>
                                </label>
                                <input type="range" id="rating" name="rating"
                                       class="form-range"
                                       min="0" max="10" step="0.1"
                                       value="<?= $game->rating ?? 5 ?>"
                                       style="accent-color:var(--accent);">
                            </div>
                        </div>

                        <!Cover URL>
                        <div class="mb-4">
                            <label for="cover_url" class="form-label">Cover URL</label>
                            <input type="url" id="cover_url" name="cover_url"
                                   class="form-control <?= isset($errors['cover_url']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($game->coverUrl ?? '') ?>">
                            <?php if (isset($errors['cover_url'])): ?>
                                <div class="invalid-feedback"><?= $errors['cover_url'] ?></div>
                            <?php endif; ?>
                            <img id="coverPreview"
                                 src="<?= htmlspecialchars($game->coverUrl ?? '') ?>"
                                 alt="Cover preview"
                                 style="display:<?= $game->coverUrl ? 'block' : 'none' ?>;
                                     max-height:180px;border-radius:10px;margin-top:.75rem;object-fit:cover;">
                        </div>

                        <!Knoppen>
                        <div class="d-flex gap-3 flex-wrap pt-2">
                            <button type="submit" class="gv-btn-primary" style="font-size:.95rem;padding:.65rem 1.5rem;">
                                <i class="bi bi-floppy"></i> Wijzigingen opslaan
                            </button>
                            <a href="show.php?id=<?= $game->id ?>" class="gv-btn-outline" style="font-size:.95rem;padding:.65rem 1.5rem;">
                                <i class="bi bi-x"></i> Annuleren
                            </a>
                        </div>

                    </form>
                </div>
            </div>

            <!Huidige cover sidebar>
            <div class="col-lg-4 fade-up fade-up-1">
                <div class="gv-form-card" style="padding:1.5rem;">
                    <h5 style="font-family:var(--font-display);font-weight:700;margin-bottom:1rem;">
                        Huidige cover
                    </h5>
                    <?php if ($game->coverUrl): ?>
                        <img src="<?= htmlspecialchars($game->coverUrl) ?>"
                             alt="Huidige cover"
                             style="width:100%;border-radius:10px;object-fit:cover;aspect-ratio:3/4;">
                    <?php else: ?>
                        <div class="gv-card-placeholder" style="height:200px;border-radius:10px;">
                            <i class="bi bi-controller"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="gv-form-card mt-3" style="padding:1.5rem;">
                    <h5 style="font-family:var(--font-display);font-weight:700;margin-bottom:.75rem;color:var(--danger);">
                        <i class="bi bi-trash"></i> Verwijderen
                    </h5>
                    <p style="font-size:.85rem;color:var(--text-secondary);">
                        Wil je deze game permanent verwijderen?
                    </p>
                    <a href="delete.php?id=<?= $game->id ?>" class="gv-btn-danger gv-btn-sm mt-1">
                        <i class="bi bi-trash"></i> Verwijder game
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php require_once '../../includes/footer.php'; ?>