<?php
/**
 * footer.php – Shared footer for GameVault
 *
 * Per RAWG Terms of Use: "add an active hyperlink from every page where the
 * data of RAWG is used."  → RAWG attribution is shown on every page.
 */

$depth = substr_count(str_replace($_SERVER['DOCUMENT_ROOT'], '', __DIR__), '/');
$root  = str_repeat('../', max(0, $depth - 1));
?>
</main><!-- /#main-content -->

<!-- Footer -->
<footer class="gv-footer" role="contentinfo">
    <div class="container">
        <div class="row align-items-start gy-4">

            <!-- Brand & info -->
            <div class="col-md-4">
                <a class="gv-brand" href="<?= $root ?>index.php" aria-label="GameVault startpagina">
                    <span class="brand-icon" aria-hidden="true"><i class="bi bi-controller"></i></span>
                    Game<span class="brand-accent">Vault</span>
                </a>
                <p class="gv-footer-sub mt-2">
                    Schoolproject – MBO ICT SD<br>
                    Koning Willem I College, Cuijk
                </p>
            </div>

            <!-- Links -->
            <div class="col-md-4">
                <nav aria-label="Footernavigatie">
                    <ul class="list-unstyled gv-footer-links">
                        <li><a href="<?= $root ?>index.php">Home</a></li>
                        <li><a href="<?= $root ?>pages/games/index.php">Games</a></li>
                        <li><a href="<?= $root ?>pages/games/create.php">Toevoegen</a></li>
                        <li><a href="<?= $root ?>pages/api/search.php">API Zoeken</a></li>
                        <li><a href="<?= $root ?>pages/privacy.php">Privacyverklaring</a></li>
                    </ul>
                </nav>
            </div>

            <!-- RAWG attribution – VERPLICHT per RAWG API Terms of Use -->
            <div class="col-md-4">
                <p class="gv-footer-sub" style="font-weight:600;color:var(--text-secondary);">
                    <i class="bi bi-shield-check" aria-hidden="true" style="color:var(--accent);"></i>
                    Databron &amp; attributie
                </p>
                <p class="gv-footer-sub mt-1">
                    Speldata en afbeeldingen worden geleverd door
                    <a href="https://rawg.io" target="_blank" rel="noopener noreferrer"
                       style="color:var(--accent);font-weight:600;">RAWG.io</a>
                    via de
                    <a href="https://rawg.io/apidocs" target="_blank" rel="noopener noreferrer"
                       style="color:var(--accent);">RAWG Video Games Database API</a>.
                </p>
                <p class="gv-footer-sub mt-1" style="font-size:.78rem;color:var(--text-muted);">
                    Geen persoonsgegevens worden opgeslagen of gedeeld.<br>
                    Schoolproject – niet-commercieel gebruik.
                </p>
            </div>
        </div>

        <hr class="gv-footer-hr">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <p class="gv-footer-copy mb-0">
                &copy; <?= date('Y') ?> GameVault &mdash; MBO SD Eindopdracht
            </p>
            <!-- RAWG attribution: required active hyperlink per API Terms of Use -->
            <p class="gv-footer-copy mb-0" style="font-size:.78rem;">
                Data: <a href="https://rawg.io" target="_blank" rel="noopener noreferrer"
                         class="rawg-badge" aria-label="RAWG Video Games Database (opent in nieuw tabblad)">
                    <i class="bi bi-controller" aria-hidden="true"></i> RAWG
                </a>
            </p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="<?= $root ?>assets/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= $root ?>assets/js/main.js"></script>

</body>
</html>
