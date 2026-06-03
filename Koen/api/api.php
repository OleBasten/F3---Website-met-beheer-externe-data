<?php
// API Configuratie
define('RAWG_API_KEY', 'd5ab7100d93c4fc7aceffb1c4c508235');
define('RAWG_BASE_URL', 'https://api.rawg.io/api');

// Functie om API calls te maken
function callRawgApi($endpoint, $params = []) {
    $params['key'] = RAWG_API_KEY;
    $url = RAWG_BASE_URL . $endpoint . '?' . http_build_query($params);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 30
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    return false;
}

// Zoek publisher ID op naam
function getPublisherId($publisherName) {
    $data = callRawgApi('/publishers', ['search' => $publisherName, 'page_size' => 5]);

    if ($data && isset($data['results']) && count($data['results']) > 0) {
        return $data['results'][0];
    }
    return null;
}

// Haal alle games op van een publisher
function getGamesByPublisher($publisherId, $page = 1, $pageSize = 40) {
    return callRawgApi('/games', [
        'publishers' => $publisherId,
        'page_size' => $pageSize,
        'page' => $page,
        'ordering' => '-released'
    ]);
}

// Verwerk AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    if ($_GET['action'] === 'search' && isset($_GET['publisher'])) {
        $publisherName = $_GET['publisher'];
        $publisher = getPublisherId($publisherName);

        if ($publisher) {
            $games = getGamesByPublisher($publisher['id']);
            echo json_encode([
                'success' => true,
                'publisher' => $publisher,
                'games' => $games ? $games['results'] : [],
                'total' => $games ? $games['count'] : 0
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Publisher niet gevonden'
            ]);
        }
        exit;
    }

    if ($_GET['action'] === 'game_details' && isset($_GET['game_id'])) {
        $gameId = $_GET['game_id'];
        $gameDetails = callRawgApi('/games/' . $gameId);
        echo json_encode($gameDetails);
        exit;
    }
}
?>