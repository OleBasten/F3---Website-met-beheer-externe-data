<?php
session_start();

require_once '../../classes/Database.php';
require_once '../../classes/GameRepository.php';

$pdo        = Database::getInstance();
$repository = new GameRepository($pdo);

$genres    = $repository->getAllGenres();
$platforms = $repository->getAllPlatforms();
$errors    = [];
$old       = [];

//Verwerk POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $title       = trim($_POST['title']        ?? '');
    $description = trim($_POST['description']  ?? '');
    $genreId     = (int) ($_POST['genre_id']    ?? 0);
    $platformId  = (int) ($_POST['platform_id'] ?? 0);
    $releaseYear = (int) ($_POST['release_year']?? 0);
    $rating      = trim($_POST['rating']        ?? '');
    $coverUrl    = trim($_POST['cover_url']     ?? '');

    if ($title === '') {
        $errors['title'] = 'Titel is verplicht.';
    } elseif (strlen($title) > 255) {
        $errors['title'] = 'Titel mag maximaal 255 tekens zijn.';
    }

    if ($releaseYear && ($releaseYear < 1970 || $releaseYear > (int)date('Y') + 2)) {
        $errors['release_year'] = 'Voer een geldig jaar in (1970–' . (date('Y') + 2) . ').';
    }

    if ($rating !== '' && (!is_numeric($rating) || $rating < 0 || $rating > 10)) {
        $errors['rating'] = 'Rating moet tussen 0 en 10 liggen.';
    }

    if ($coverUrl && !filter_var($coverUrl, FILTER_VALIDATE_URL)) {
        $errors['cover_url'] = 'Voer een geldige URL in.';
    }

    //Geen fouten → opslaan
    if (empty($errors)) {
        $id = $repository->create([
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
            'message' => "'{$title}' is succesvol toegevoegd!"
        ];
        header("Location: show.php?id={$id}");
        exit;
    }
}

$pageTitle = 'Game toevoegen – Vault';
require_once '../../includes/header.php';
?>

    <!Formulier>
    <div class="container gv-form-page">

        <div class="gv-form-header">
            <p class="gv-breadcrumb">
                <a href="../../index.php">Home</a> /
                <a href="index.php">Games</a> / Toevoegen
            </p>
            <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;margin-top:.5rem;">
                <i class="bi bi-plus-circle text-accent"></i> Game toevoegen
            </h1>
            <p style="color:var(--text-secondary);margin-top:.5rem;">
                Vul de gegevens in om een nieuwe game aan de collectie toe te voegen.
            </p>
        </div>

        <div class="row g-4">

            <!Formulier kolom>
            <div class="col-lg-8">
                <div class="gv-form-card fade-up">
                    <form method="POST" novalidate>

                        <!Titel>
                        <div class="mb-4">
                            <label for="title" class="form-label">Titel *</label>
                            <input type="text" id="title" name="title"
                                   class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['title'] ?? '') ?>"
                                   placeholder="bijv. Hollow Knight"
                                   maxlength="255" required>
                            <?php if (isset($errors['title'])): ?>
                                <div class="invalid-feedback"><?= $errors['title'] ?></div>
                            <?php endif; ?>
                        </div>

                        <!Beschrijving>
                        <div class="mb-4">
                            <label for="description" class="form-label">Beschrijving</label>
                            <textarea id="description" name="description"
                                      class="form-control" rows="4"
                                      placeholder="Korte omschrijving van de game…"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                        </div>

                        <!Genre + Platform>
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label for="genre_id" class="form-label">Genre</label>
                                <select id="genre_id" name="genre_id" class="form-select">
                                    <option value="">– Selecteer genre –</option>
                                    <?php foreach ($genres as $g): ?>
                                        <option value="<?= $g['id'] ?>"
                                            <?= ($old['genre_id'] ?? '') == $g['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($g['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label for="platform_id" class="form-label">Platform</label>
                                <select id="platform_id" name="platform_id" class="form-select">
                                    <option value="">– Selecteer platform –</option>
                                    <?php foreach ($platforms as $p): ?>
                                        <option value="<?= $p['id'] ?>"
                                            <?= ($old['platform_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
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
                                       value="<?= htmlspecialchars($old['release_year'] ?? '') ?>"
                                       min="1970" max="<?= date('Y') + 2 ?>"
                                       placeholder="<?= date('Y') ?>">
                                <?php if (isset($errors['release_year'])): ?>
                                    <div class="invalid-feedback"><?= $errors['release_year'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-sm-6">
                                <label for="rating" class="form-label">
                                    Rating (0–10)
                                    <span id="ratingDisplay"
                                          style="color:var(--warning);font-weight:700;">
                                    <?= htmlspecialchars($old['rating'] ?? '') ?: '–' ?>
                                </span>
                                </label>
                                <input type="range" id="rating" name="rating"
                                       class="form-range <?= isset($errors['rating']) ? 'is-invalid' : '' ?>"
                                       min="0" max="10" step="0.1"
                                       value="<?= htmlspecialchars($old['rating'] ?? '5') ?>"
                                       style="accent-color:var(--accent);">
                                <?php if (isset($errors['rating'])): ?>
                                    <div class="invalid-feedback"><?= $errors['rating'] ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!Cover URL>
                        <div class="mb-4">
                            <label for="cover_url" class="form-label">Cover URL</label>
                            <input type="url" id="cover_url" name="cover_url"
                                   class="form-control <?= isset($errors['cover_url']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['cover_url'] ?? '') ?>"
                                   placeholder="https://...">
                            <?php if (isset($errors['cover_url'])): ?>
                                <div class="invalid-feedback"><?= $errors['cover_url'] ?></div>
                            <?php endif; ?>
                            <!-- Live preview -->
                            <img id="coverPreview"
                                 src="<?= htmlspecialchars($old['cover_url'] ?? '') ?>"
                                 alt="Cover preview"
                                 style="display:<?= !empty($old['cover_url']) ? 'block' : 'none' ?>;
                                     max-height:180px;border-radius:10px;margin-top:.75rem;object-fit:cover;">
                        </div>

                        <!Knoppen>
                        <div class="d-flex gap-3 flex-wrap pt-2">
                            <button type="submit" class="gv-btn-primary" style="font-size:.95rem;padding:.65rem 1.5rem;">
                                <i class="bi bi-floppy"></i> Game opslaan
                            </button>
                            <a href="index.php" class="gv-btn-outline" style="font-size:.95rem;padding:.65rem 1.5rem;">
                                <i class="bi bi-x"></i> Annuleren
                            </a>
                        </div>

                    </form>
                </div>
            </div>

            <!Tips sidebar>
            <div class="col-lg-4 fade-up fade-up-1">
                <div class="gv-form-card" style="padding:1.75rem;">
                    <h5 style="font-family:var(--font-display);font-weight:700;margin-bottom:1rem;">
                        <i class="bi bi-lightbulb text-accent"></i> Tips
                    </h5>
                    <ul style="padding-left:1.1rem;color:var(--text-secondary);font-size:.875rem;line-height:1.8;">
                        <li>Titel is het enige verplichte veld.</li>
                        <li>Gebruik een directe afbeeldings-URL voor de cover.</li>
                        <li>Rating van 0 tot 10 (decimalen toegestaan).</li>
                        <li>Via de API-zoekpagina kun je games importeren.</li>
                    </ul>

                    <hr style="border-color:var(--border);margin:1.25rem 0;">

                    <h5 style="font-family:var(--font-display);font-weight:700;margin-bottom:.75rem;">
                        <i class="bi bi-cloud text-accent"></i> RAWG API
                    </h5>
                    <p style="color:var(--text-secondary);font-size:.85rem;margin:0;">
                        Zoek en importeer games direct vanuit de RAWG-database.
                    </p>
                    <a href="../api/search.php" class="gv-btn-outline gv-btn-sm mt-3">
                        <i class="bi bi-search"></i> API zoeken
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php require_once '../../includes/footer.php'; ?>