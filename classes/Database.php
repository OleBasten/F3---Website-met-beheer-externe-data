<?php
require_once __DIR__ . '/../config/database.php';

class Database
{
    //Singleton: slechts één verbinding per request
    private static ?PDO $instance = null;

    //Constructor privé → niemand kan `new Database()` doen
    private function __construct() {}

    /**
     * Geeft de PDO-instantie terug.
     * Maakt de verbinding aan als deze nog niet bestaat.
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // gooi PDOException bij fouten
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // fetch als associatieve array
                PDO::ATTR_EMULATE_PREPARES   => false,                   // gebruik native prepared statements
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                //Verberg technische details voor eindgebruikers
                error_log('Database verbinding mislukt: ' . $e->getMessage());
                die(json_encode(['error' => 'Kan geen verbinding maken met de database.']));
            }
        }

        return self::$instance;
    }
}