<?php

/**
 * Database klasse
 * Regelt ALLEEN de verbinding met MySQL
 * Hoef je maar 1x aan te passen als je host/wachtwoord verandert
 */
class Database {

    private mysqli $conn;

    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "", "vault");

        if ($this->conn->connect_error) {
            die("Verbinding mislukt: " . $this->conn->connect_error);
        }
    }

    // Geeft de verbinding terug zodat andere klassen hem kunnen gebruiken
    public function getConnection(): mysqli {
        return $this->conn;
    }

    public function close(): void {
        $this->conn->close();
    }
}