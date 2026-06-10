<?php

require_once __DIR__ . '/Game.php';

class GameRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    //READ

    public function getAll(string $search = '', string $orderBy = 'g.title'): array
    {
        $allowed = ['g.title', 'g.rating', 'g.release_year', 'genres.name'];
        if (!in_array($orderBy, $allowed)) {
            $orderBy = 'g.title';
        }

        $sql = "
            SELECT
                g.*,
                genres.name   AS genre_name,
                platforms.name AS platform_name
            FROM   games g
            LEFT JOIN genres    ON g.genre_id    = genres.id
            LEFT JOIN platforms ON g.platform_id = platforms.id
        ";

        if ($search !== '') {
            $sql .= " WHERE g.title LIKE :search OR genres.name LIKE :search";
        }

        $sql .= " ORDER BY {$orderBy} ASC";

        $stmt = $this->pdo->prepare($sql);

        if ($search !== '') {
            $stmt->bindValue(':search', '%' . $search . '%');
        }

        $stmt->execute();
        return array_map(fn($row) => new Game($row), $stmt->fetchAll());
    }

    public function getById(int $id): ?Game
    {
        $stmt = $this->pdo->prepare("
            SELECT
                g.*,
                genres.name    AS genre_name,
                platforms.name AS platform_name
            FROM   games g
            LEFT JOIN genres    ON g.genre_id    = genres.id
            LEFT JOIN platforms ON g.platform_id = platforms.id
            WHERE  g.id = :id
            LIMIT  1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? new Game($row) : null;
    }

    //CREATE

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO games
                (title, description, genre_id, platform_id, release_year, rating, cover_url, rawg_id)
            VALUES
                (:title, :description, :genre_id, :platform_id, :release_year, :rating, :cover_url, :rawg_id)
        ");
        $stmt->execute([
            ':title'        => $data['title'],
            ':description'  => $data['description']  ?? null,
            ':genre_id'     => $data['genre_id']      ?: null,
            ':platform_id'  => $data['platform_id']   ?: null,
            ':release_year' => $data['release_year']  ?: null,
            ':rating'       => $data['rating']        ?: null,
            ':cover_url'    => $data['cover_url']     ?: null,
            ':rawg_id'      => $data['rawg_id']       ?: null,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    //UPDATE

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE games
            SET
                title        = :title,
                description  = :description,
                genre_id     = :genre_id,
                platform_id  = :platform_id,
                release_year = :release_year,
                rating       = :rating,
                cover_url    = :cover_url
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'           => $id,
            ':title'        => $data['title'],
            ':description'  => $data['description']  ?? null,
            ':genre_id'     => $data['genre_id']      ?: null,
            ':platform_id'  => $data['platform_id']   ?: null,
            ':release_year' => $data['release_year']  ?: null,
            ':rating'       => $data['rating']        ?: null,
            ':cover_url'    => $data['cover_url']     ?: null,
        ]);
    }

    //DELETE

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM games WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    //HELPERS

    public function getAllGenres(): array
    {
        return $this->pdo->query("SELECT * FROM genres ORDER BY name")->fetchAll();
    }

    public function getAllPlatforms(): array
    {
        return $this->pdo->query("SELECT * FROM platforms ORDER BY name")->fetchAll();
    }

    public function count(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM games")->fetchColumn();
    }

    public function avgRating(): float
    {
        return (float) $this->pdo->query("SELECT AVG(rating) FROM games")->fetchColumn();
    }
}