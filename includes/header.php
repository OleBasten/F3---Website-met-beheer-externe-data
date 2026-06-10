<?php
/**
 * header.php – Shared page header for GameVault
 *
 * Security improvements (2026):
 *  - Sends HTTP security headers
 *  - CSRF token is generated each session
 *  - Skip-to-content link for accessibility (WCAG 2.1 AA)
 *  - RAWG attribution on every page (per RAWG Terms of Use)
 */

require_once __DIR__ . '/../includes/security.php';
security_send_headers();

// Tel hoe diep de huidige pagina in mappen zit t.o.v. de root
$currentPath = dirname($_SERVER['PHP_SELF']);
$levels = substr_count(trim($currentPath, '/'), '/');
$root = str_repeat('../', $levels);

// Huidige pagina detecteren voor actieve nav-link
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

// Zorg dat CSRF token beschikbaar is
csrf_token();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? 'GameVault – Beheer jouw gamecollectie. Schoolproject MBO ICT SD.') ?>">
    <!-- Security: geen zoekmachine-indexering voor schoolproject -->
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars($pageTitle ?? 'Vault') ?></title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= $root ?>assets/css/bootstrap.min.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= $root ?>assets/css/node_modules/bootstrap-icons/font/bootstrap-icons.css">

    <!-- Google Fonts: Outfit (display) + DM Sans (body) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
          rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $root ?>assets/css/style.css">
</head>
<body>

<!-- Skip-to-content (WCAG 2.1 AA – toetsenbordtoegankelijkheid) -->
<a href="#main-content" class="skip-to-content">Ga naar hoofdinhoud</a>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg gv-navbar sticky-top" role="navigation" aria-label="Hoofdnavigatie">
    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand gv-brand" href="<?= $root ?>index.php" aria-label="GameVault – Terug naar startpagina">
            <span class="brand-icon" aria-hidden="true"><i class="bi bi-controller"></i></span>
            Game<span class="brand-accent">Vault</span>
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler gv-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false"
                aria-label="Menu openen">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>

        <!-- Links -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

                <li class="nav-item">
                    <a class="nav-link gv-nav-link <?= ($currentDir === 'vault' || $currentPage === 'index') ? 'active' : '' ?>"
                       href="<?= $root ?>index.php"
                       <?= ($currentDir === 'vault' || $currentPage === 'index') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-house" aria-hidden="true"></i> Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link gv-nav-link <?= ($currentDir === 'games') ? 'active' : '' ?>"
                       href="<?= $root ?>pages/games/index.php"
                       <?= ($currentDir === 'games') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-grid" aria-hidden="true"></i> Games
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link gv-nav-link <?= ($currentPage === 'search') ? 'active' : '' ?>"
                       href="<?= $root ?>pages/api/search.php"
                       <?= ($currentPage === 'search') ? 'aria-current="page"' : '' ?>>
                        <i class="bi bi-search" aria-hidden="true"></i> API Zoeken
                    </a>
                </li>

                <li class="nav-item ms-lg-2">
                    <a class="btn gv-btn-primary btn-sm"
                       href="<?= $root ?>pages/games/create.php">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> Game toevoegen
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<!-- Flash berichten -->
<?php if (!empty($_SESSION['flash'])): ?>
    <div role="alert" aria-live="polite" aria-atomic="true">
    <?php foreach ($_SESSION['flash'] as $flash): ?>
        <div class="alert gv-flash gv-flash-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show"
             role="alert">
            <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"
               aria-hidden="true"></i>
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"
                    aria-label="Melding sluiten"></button>
        </div>
    <?php endforeach; ?>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!-- Main content -->
<main id="main-content" class="gv-main">
