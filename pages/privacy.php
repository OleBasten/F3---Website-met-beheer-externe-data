<?php
/**
 * privacy.php – Privacyverklaring GameVault
 *
 * Vereist voor hosting in Nederland (AVG/GDPR – 2026).
 * Schoolproject: geen persoonsgegevens worden verwerkt.
 */

require_once '../includes/security.php';
security_configure_session();
session_start();

$pageTitle       = 'Privacyverklaring – Vault';
$pageDescription = 'Privacyverklaring van GameVault: welke data wordt verwerkt en hoe.';
require_once '../includes/header.php';
?>

<div class="container" style="max-width:780px;padding:3rem 1rem 5rem;">

    <p class="gv-breadcrumb">
        <a href="../index.php">Home</a> /
        <span aria-current="page">Privacyverklaring</span>
    </p>

    <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;margin:.5rem 0 .25rem;">
        <i class="bi bi-shield-check text-accent" aria-hidden="true"></i>
        Privacyverklaring
    </h1>
    <p style="color:var(--text-muted);font-size:.85rem;">
        Laatst bijgewerkt: <?= date('d F Y') ?>
    </p>

    <div class="gv-form-card mt-4" style="padding:2rem;">

        <section aria-labelledby="sec-wie">
            <h2 id="sec-wie" style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem;">
                1. Wie zijn wij?
            </h2>
            <p style="color:var(--text-secondary);font-size:.9rem;line-height:1.7;">
                GameVault is een schoolproject van een student aan het
                <strong>Koning Willem I College</strong> (MBO ICT Software Developer, Cuijk).
                Dit project is niet-commercieel en bedoeld als eindopdracht.
            </p>
        </section>

        <hr style="border-color:var(--border);margin:1.5rem 0;">

        <section aria-labelledby="sec-data">
            <h2 id="sec-data" style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem;">
                2. Welke gegevens worden verwerkt?
            </h2>
            <p style="color:var(--text-secondary);font-size:.9rem;line-height:1.7;">
                GameVault verwerkt <strong>geen persoonsgegevens</strong>.
                De applicatie slaat uitsluitend spelgerelateerde data op
                (naam, genre, platform, rating, coverfoto-URL) in een
                lokale MySQL-database.
            </p>
            <p style="color:var(--text-secondary);font-size:.9rem;line-height:1.7;">
                Er zijn geen gebruikersaccounts, geen login, geen trackers
                en geen analyticsscripts actief op deze applicatie.
            </p>
        </section>

        <hr style="border-color:var(--border);margin:1.5rem 0;">

        <section aria-labelledby="sec-cookies">
            <h2 id="sec-cookies" style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem;">
                3. Cookies &amp; sessies
            </h2>
            <p style="color:var(--text-secondary);font-size:.9rem;line-height:1.7;">
                Deze applicatie maakt gebruik van een <strong>PHP-sessiecookie</strong>
                om meldingen (flash messages) en CSRF-beveiliging mogelijk te maken.
                Dit cookie bevat <em>geen persoonsgegevens</em> en wordt verwijderd
                wanneer je de browser sluit.
            </p>
            <table class="table table-sm mt-3" style="font-size:.83rem;color:var(--text-secondary);">
                <caption class="visually-hidden">Overzicht van gebruikte cookies</caption>
                <thead>
                    <tr>
                        <th>Naam</th><th>Type</th><th>Doel</th><th>Vervalt</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>PHPSESSID</code></td>
                        <td>Sessiecookie</td>
                        <td>Flash-meldingen &amp; CSRF-beveiliging</td>
                        <td>Sessie (browser sluiten)</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <hr style="border-color:var(--border);margin:1.5rem 0;">

        <section aria-labelledby="sec-rawg">
            <h2 id="sec-rawg" style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem;">
                4. RAWG API – externe databron
            </h2>
            <p style="color:var(--text-secondary);font-size:.9rem;line-height:1.7;">
                Speldata en afbeeldingen worden opgehaald via de
                <a href="https://rawg.io/apidocs" target="_blank" rel="noopener noreferrer"
                   style="color:var(--accent);">RAWG Video Games Database API</a>.
                Wanneer je de zoekfunctie gebruikt, wordt je zoekopdracht
                naar de RAWG-servers verzonden. RAWG heeft een eigen
                <a href="https://rawg.io/privacy" target="_blank" rel="noopener noreferrer"
                   style="color:var(--accent);">privacybeleid</a>.
                Er worden <em>geen persoonsgegevens</em> doorgestuurd naar RAWG.
            </p>
        </section>

        <hr style="border-color:var(--border);margin:1.5rem 0;">

        <section aria-labelledby="sec-security">
            <h2 id="sec-security" style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem;">
                5. Beveiliging
            </h2>
            <ul style="color:var(--text-secondary);font-size:.9rem;line-height:1.9;padding-left:1.2rem;">
                <li>CSRF-tokens op alle formulieren</li>
                <li>PDO prepared statements (SQL-injection preventie)</li>
                <li>HTTP-beveiligingsheaders (CSP, X-Frame-Options, enz.)</li>
                <li>Invoervalidatie aan server- en clientzijde</li>
                <li>Sessiecookies met HttpOnly- en SameSite-vlaggen</li>
            </ul>
        </section>

        <hr style="border-color:var(--border);margin:1.5rem 0;">

        <section aria-labelledby="sec-contact">
            <h2 id="sec-contact" style="font-size:1.1rem;font-weight:700;margin-bottom:.75rem;">
                6. Contact
            </h2>
            <p style="color:var(--text-secondary);font-size:.9rem;line-height:1.7;">
                Vragen over deze verklaring? Neem contact op via je docent of de
                school: <strong>Koning Willem I College, Cuijk</strong>.
            </p>
        </section>

    </div>

    <div class="mt-3">
        <a href="../index.php" class="gv-btn-outline gv-btn-sm">
            <i class="bi bi-arrow-left" aria-hidden="true"></i> Terug naar home
        </a>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
