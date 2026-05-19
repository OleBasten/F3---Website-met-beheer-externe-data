<?php
//Tel hoe diep de huidige pagina in mappen zit t.o.v. de root
//We halen de bestandsnaam eraf en tellen de slashes
$currentPath = dirname($_SERVER['PHP_SELF']);
$levels = substr_count(trim($currentPath, '/'), '/');

//Als je project in een submap staat (zoals /mijn-project/),
//moet je soms een extra stapje terug doen of juist niet.
//Voor de meeste lokale servers (localhost/project) werkt dit:
$root = str_repeat('../', $levels);

//Huidige pagina detecteren voor actieve nav-link
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Vault - Beheer jouw gamecollectie">
    <title><?= htmlspecialchars($pageTitle ?? 'Vault') ?></title>

    <!Bootstrap 5>
    <link rel="stylesheet" href="<?= $root ?>assets/css/bootstrap.min.css">

    <!Bootstrap Icons>
    <link rel="stylesheet" href="<?= $root ?>assets/css/node_modules/bootstrap-icons/font/bootstrap-icons.css">

    <!Google Fonts: Outfit (display) + DM Sans (body)>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap"
          rel="stylesheet">

    <!Custom CSS>
    <link rel="stylesheet" href="<?= $root ?>assets/css/style.css">
</head>
<body>

<!Navbar>
<nav class="navbar navbar-expand-lg gv-navbar sticky-top">
    <div class="container">

        <!Logo>
        <a class="navbar-brand gv-brand" href="<?= $root ?>index.php">
            <span class="brand-icon"><i class="bi bi-controller"></i></span>
            Game<span class="brand-accent">Vault</span>
        </a>

        <!Mobile toggle>
        <button class="navbar-toggler gv-toggler" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false"
                aria-label="Menu openen">
            <span></span><span></span><span></span>
        </button>

        <!Links>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">

                <li class="nav-item">
                    <a class="nav-link gv-nav-link <?= ($currentDir === 'vault' || $currentPage === 'index') ? 'active' : '' ?>"
                       href="<?= $root ?>index.php">
                        <i class="bi bi-house"></i> Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link gv-nav-link <?= ($currentDir === 'games') ? 'active' : '' ?>"
                       href="<?= $root ?>pages/games/index.php">
                        <i class="bi bi-grid"></i> Games
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link gv-nav-link <?= ($currentPage === 'search') ? 'active' : '' ?>"
                       href="<?= $root ?>pages/api/search.php">
                        <i class="bi bi-search"></i> API Zoeken
                    </a>
                </li>

                <li class="nav-item ms-lg-2">
                    <a class="btn gv-btn-primary btn-sm"
                       href="<?= $root ?>pages/games/create.php">
                        <i class="bi bi-plus-lg"></i> Game toevoegen
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<!Flash berichten (Week 3: Feedbackmeldingen)>
<?php if (!empty($_SESSION['flash'])): ?>
    <?php foreach ($_SESSION['flash'] as $flash): ?>
        <div class="alert gv-flash gv-flash-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show"
             role="alert">
            <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
            <?= htmlspecialchars($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!Main content>
<main class="gv-main">