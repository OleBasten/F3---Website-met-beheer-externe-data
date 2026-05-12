<?php

class Game
{
    public int     $id;
    public string  $title;
    public string  $description;
    public ?int    $genreId;
    public ?string $genreName;
    public ?int    $platformId;
    public ?string $platformName;
    public ?int    $releaseYear;
    public ?float  $rating;
    public ?string $coverUrl;
    public ?int    $rawgId;
    public string  $createdAt;
    public string  $updatedAt;

    public function __construct(array $data)
    {
        $this->id           = (int)  ($data['id']            ?? 0);
        $this->title        =        ($data['title']         ?? '');
        $this->description  =        ($data['description']   ?? '');
        $this->genreId      = isset($data['genre_id'])    ? (int)   $data['genre_id']    : null;
        $this->genreName    =        ($data['genre_name']    ?? null);
        $this->platformId   = isset($data['platform_id']) ? (int)   $data['platform_id'] : null;
        $this->platformName =        ($data['platform_name'] ?? null);
        $this->releaseYear  = isset($data['release_year']) ? (int)  $data['release_year'] : null;
        $this->rating       = isset($data['rating'])       ? (float)$data['rating']       : null;
        $this->coverUrl     =        ($data['cover_url']    ?? null);
        $this->rawgId       = isset($data['rawg_id'])      ? (int)  $data['rawg_id']      : null;
        $this->createdAt    =        ($data['created_at']   ?? '');
        $this->updatedAt    =        ($data['updated_at']   ?? '');
    }

    public function getStars(): string
    {
        if ($this->rating === null) {
            return 'N/A';
        }
        $stars = round($this->rating / 2); // schaal 0–10 → 0–5 sterren
        return str_repeat('★', $stars) . str_repeat('☆', 5 - $stars);
    }

    public function getCoverUrl(): string
    {
        return $this->coverUrl
            ?: 'assets/images/placeholder.svg';
    }
}