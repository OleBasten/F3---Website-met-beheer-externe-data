<?php

$depth = substr_count(str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__), '/');
$root  = str_repeat('../', max(0, $depth - 1));
?>
</main>

<!-- ── Footer ──────────────────────────────────────────────── -->
<footer class="gv-footer">
    <div class="container">
        <div class="row align-items-center gy-3">

            <div class="col-md-4">
                <a class="gv-brand" href="<?= $root ?>index.php">
                    <span class="brand-icon"><i class="bi bi-controller"></i></span>
                    Game<span class="brand-accent">Vault</span>
                </a>
                <p class="gv-footer-sub mt-1">
                    Schoolproject – MBO ICT SD<br>
                    Koning Willem I College
                </p>
            </div>

            <div class="col-md-4 text-md-center">
                <ul class="list-unstyled gv-footer-links">
                    <li><a href="<?= $root ?>index.php">Home</a></li>
                    <li><a href="<?= $root ?>pages/games/index.php">Games</a></li>
                    <li><a href="<?= $root ?>pages/games/create.php">Toevoegen</a></li>
                    <li><a href="<?= $root ?>pages/api/search.php">API Zoeken</a></li>
                </ul>
            </div>
        </div>

        <hr class="gv-footer-hr">

        <p class="gv-footer-copy text-center">
            &copy; <?= date('Y') ?> Vault &mdash; MBO SD Eindopdracht
        </p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="<?= $root ?>assets/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= $root ?>assets/js/main.js"></script>

</body>
</html>