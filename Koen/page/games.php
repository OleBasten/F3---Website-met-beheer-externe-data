<?php
require_once '../api/api.php';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Publisher Database - RAWG API</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Game Publisher Database</h1>
        <p>Alle games van een publisher - RAWG API Database</p>
        <div class="api-info">
            Gebruikt RAWG API - Meer dan 500,000+ games in de database
        </div>
    </div>

    <div class="publisher-selector">
        <div class="search-box">
            <label for="publisherInput" class="sr-only">Publisher naam</label>
            <input type="text" id="publisherInput"
                   placeholder="Voer publisher naam in (bijv: Nintendo, Sony, Rockstar Games...)" />
            <button onclick="searchGames()">Zoek Games</button>
        </div>
        <div class="popular-publishers">
            <h3>Populaire Publishers:</h3>
            <div class="publisher-tags">
                <span class="publisher-tag" onclick="quickSearch('Nintendo')">Nintendo</span>
                <span class="publisher-tag" onclick="quickSearch('Sony Interactive Entertainment')">Sony</span>
                <span class="publisher-tag" onclick="quickSearch('Microsoft Studios')">Microsoft</span>
                <span class="publisher-tag" onclick="quickSearch('Electronic Arts')">EA</span>
                <span class="publisher-tag" onclick="quickSearch('Ubisoft')">Ubisoft</span>
                <span class="publisher-tag" onclick="quickSearch('Rockstar Games')">Rockstar</span>
                <span class="publisher-tag" onclick="quickSearch('Activision')">Activision</span>
                <span class="publisher-tag" onclick="quickSearch('Square Enix')">Square Enix</span>
                <span class="publisher-tag" onclick="quickSearch('Capcom')">Capcom</span>
                <span class="publisher-tag" onclick="quickSearch('Sega')">Sega</span>
                <span class="publisher-tag" onclick="quickSearch('Bethesda Softworks')">Bethesda</span>
                <span class="publisher-tag" onclick="quickSearch('Take-Two Interactive')">Take-Two</span>
            </div>
        </div>
    </div>

    <div id="stats" class="stats"></div>
    <div id="content" class="games-grid"></div>
</div>

<div id="gameModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Game Details</h2>
        </div>
        <div class="modal-body" id="modalBody">Laden...</div>
    </div>
</div>

<footer class="gv-footer">
    <p class="gv-footer-copy text-center small mt-2">
        <a href="https://rawg.io" target="_blank" rel="noopener noreferrer">RAWG</a>
    </p>
</footer>

<script>
    async function searchGames() {
        const publisher = document.getElementById('publisherInput').value.trim();
        if (!publisher) { showError('Voer een publisher naam in!'); return; }
        showLoading();

        try {
            // Stuurt het verzoek naar api.php via de query string
            const response = await fetch(`../api/api.php?action=search&publisher=${encodeURIComponent(publisher)}`);
            const data = await response.json();

            if (data.success) {
                displayGames(data.games, data.publisher);
                updateStats(data.games, data.publisher, data.total);
            } else {
                showError(data.message || 'Geen games gevonden');
                document.getElementById('content').innerHTML = '<div class="no-results">Geen publisher gevonden.</div>';
                document.getElementById('stats').style.display = 'none';
            }
        } catch (error) {
            console.error('Error:', error);
            showError('Fout bij het laden van data. Probeer het later opnieuw.');
            document.getElementById('content').innerHTML = '<div class="no-results">Er is een fout opgetreden.</div>';
            document.getElementById('stats').style.display = 'none';
        }
    }

    async function quickSearch(publisher) {
        document.getElementById('publisherInput').value = publisher;
        await searchGames();
    }

    function displayGames(games, publisher) {
        const contentDiv = document.getElementById('content');
        if (!games || games.length === 0) {
            contentDiv.innerHTML = '<div class="no-results">Geen games gevonden voor deze publisher</div>';
            return;
        }
        contentDiv.innerHTML = games.map(game => `
            <div class="game-card" onclick="showGameDetails(${game.id})">
                <img class="game-image"
                     src="${game.background_image || 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=400&h=300&fit=crop'}"
                     alt="${game.name.replace(/'/g, "\\'")}"
                     onerror="this.src='https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=400&h=300&fit=crop'">
                <div class="game-info">
                    <div class="game-title">${game.name}</div>
                    <div class="game-publisher">${publisher.name}</div>
                    <div class="game-year">${game.released ? game.released.split('-')[0] : 'Onbekend'}</div>
                    ${game.rating ? `<div class="game-rating">${game.rating}/5</div>` : ''}
                </div>
            </div>
        `).join('');
    }

    function updateStats(games, publisher, total) {
        const statsDiv = document.getElementById('stats');
        const releaseYears = games.filter(g => g.released).map(g => parseInt(g.released.split('-')[0]));
        const uniqueYears = [...new Set(releaseYears)].sort();
        const avgRating = (games.filter(g => g.rating).reduce((sum, g) => sum + g.rating, 0) / games.filter(g => g.rating).length).toFixed(1);

        statsDiv.style.display = 'block';
        statsDiv.innerHTML = `
            <h3>Statistieken voor ${publisher.name}</h3>
            <p>Getoonde games: ${games.length} van de ${total} totaal |
               Jaar range: ${uniqueYears[0] || 'N/A'} - ${uniqueYears[uniqueYears.length-1] || 'N/A'} |
               Gemiddelde rating: ${avgRating || 'N/A'}/5</p>
            <p>Data bron: RAWG API | Laatste update: ${new Date().toLocaleString()}</p>
        `;
    }

    async function showGameDetails(gameId) {
        const modal    = document.getElementById('gameModal');
        const modalBody  = document.getElementById('modalBody');
        const modalTitle = document.getElementById('modalTitle');

        modal.style.display = 'block';
        modalBody.innerHTML = '<div class="loading">Laden van game details...</div>';

        try {
            const response = await fetch(`../api/api.php?action=game_details&game_id=${gameId}`);
            const game = await response.json();

            modalTitle.innerHTML = game.name;
            modalBody.innerHTML = `
                <div style="text-align:center;">
                    ${game.background_image ? `<img src="${game.background_image}" style="width:100%;border-radius:10px;margin-bottom:15px;" alt="${game.name}">` : ''}
                    <p><strong>Release datum:</strong> ${game.released || 'Onbekend'}</p>
                    <p><strong>Rating:</strong> ${game.rating || 'N/A'}/5 (${game.ratings_count || 0} stemmen)</p>
                    <p><strong>Genres:</strong> ${game.genres ? game.genres.map(g => g.name).join(', ') : 'N/A'}</p>
                    <p><strong>Platforms:</strong> ${game.platforms ? game.platforms.slice(0,5).map(p => p.platform.name).join(', ') : 'N/A'}</p>
                    <p><strong>Ontwikkelaars:</strong> ${game.developers ? game.developers.map(d => d.name).join(', ') : 'N/A'}</p>
                    <p><strong>Publishers:</strong> ${game.publishers ? game.publishers.map(p => p.name).join(', ') : 'N/A'}</p>
                    <p><strong>Beschrijving:</strong></p>
                    <p style="text-align:left;">${game.description_raw || 'Geen beschrijving beschikbaar'}</p>
                    ${game.website ? `<p><strong>Website:</strong> <a href="${game.website}" target="_blank">${game.website}</a></p>` : ''}
                    <p><strong>Metacritic score:</strong> ${game.metacritic || 'N/A'}</p>
                </div>
            `;
        } catch (error) {
            modalBody.innerHTML = '<div class="error-message">Fout bij het laden van game details</div>';
        }
    }

    function showLoading() {
        document.getElementById('content').innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
                <p>Zoeken in RAWG database...</p>
                <p style="font-size:0.9em;margin-top:10px;">Doorzoeken van 500,000+ games...</p>
            </div>
        `;
        document.getElementById('stats').style.display = 'none';
    }

    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = `⚠ ${message}`;
        const container = document.querySelector('.container');
        const existingError = document.querySelector('.error-message');
        if (existingError) existingError.remove();
        container.insertBefore(errorDiv, document.getElementById('content'));
        setTimeout(() => errorDiv.remove(), 8000);
    }

    const modal    = document.getElementById('gameModal');
    const closeBtn = document.getElementsByClassName('close')[0];
    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick  = (e) => { if (e.target === modal) modal.style.display = 'none'; }

    document.getElementById('publisherInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') searchGames();
    });

    window.addEventListener('load', () => {
        setTimeout(() => quickSearch('Nintendo'), 500);
    });
</script>
</body>
</html>