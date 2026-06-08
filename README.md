Vault
Vault is een webapplicatie ontwikkeld voor het beheren van een persoonlijke gamecollectie. De applicatie stelt gebruikers in staat om games handmatig toe te voegen aan een database of informatie op te halen via de externe RAWG API. Dit project is ontwikkeld als onderdeel van de opleiding MBO ICT Software Development aan het Koning Willem I College.

Functionaliteiten
Dashboard: Overzicht van de totale collectie, gemiddelde ratings en recente toevoegingen.

CRUD-systeem: Volledige ondersteuning voor het aanmaken, bekijken, bewerken en verwijderen van games.

API-integratie: Zoekfunctionaliteit gekoppeld aan de RAWG API voor het ophalen van officiële gamedata.

Zoeken en Filteren: Uitgebreide zoekbalk en sorteermogelijkheden op basis van titel, rating, jaar en genre.

Responsive Design: De interface is volledig geoptimaliseerd voor desktop, tablet en mobiele apparaten.

Technieken
Backend: PHP 8.x (Object Georiënteerd Programmeren)

Database: MySQL / MariaDB (via PDO verbinding)

Frontend: HTML5, CSS3 (Custom Styles), Bootstrap 5

Icons: Bootstrap Icons

API: RAWG Video Games Database API

Projectstructuur
assets/: Bevat CSS, JavaScript en afbeeldingen.

classes/: Bevat de PHP-klassen zoals de Database connectie en Repositories.

includes/: Bevat herbruikbare componenten zoals de header en footer.

pages/: Bevat de specifieke pagina's voor het beheren en bekijken van games.

index.php: De landingspagina van de applicatie.

Installatie
Clone deze repository naar de lokale webserver map (bijv. htdocs).

Importeer het meegeleverde SQL-bestand in de database omgeving (phpMyAdmin).

Configureer de databasegegevens in classes/Database.php.

Open de applicatie via de browser op localhost/Vault.

Projectinformatie
Opleiding: MBO, ICT, Software Development

Instelling: Koning Willem I College

Vak: Website met beheer van externe data (F3)

Jaar: 2026
