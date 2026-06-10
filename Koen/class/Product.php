<?php

/**
 * Product klasse
 * Bevat alle database-acties voor producten
 * Elke functie doet precies 1 ding
 */
class Product {

    private mysqli $conn;

    // Ontvangt de database verbinding van Database.php
    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    // ── READ ───────────────────────────────────────────
    // Haal alle producten op, nieuwste eerst
    public function getAll(): mysqli_result {
        return $this->conn->query("SELECT * FROM products ORDER BY id DESC");
    }

    // ── CREATE ─────────────────────────────────────────
    // Voeg een nieuw product toe
    public function create(string $name, float $price): void {
        $stmt = $this->conn->prepare("INSERT INTO products (name, price) VALUES (?, ?)");
        $stmt->bind_param("sd", $name, $price); // s = string, d = decimaal
        $stmt->execute();
        $stmt->close();
    }

    // ── UPDATE ─────────────────────────────────────────
    // Pas een bestaand product aan op basis van id
    public function update(int $id, string $name, float $price): void {
        $stmt = $this->conn->prepare("UPDATE products SET name=?, price=? WHERE id=?");
        $stmt->bind_param("sdi", $name, $price, $id); // s = string, d = decimaal, i = integer
        $stmt->execute();
        $stmt->close();
    }

    // ── DELETE ─────────────────────────────────────────
    // Verwijder een product op basis van id
    public function delete(int $id): void {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id); // i = integer
        $stmt->execute();
        $stmt->close();
    }
}