
CREATE DATABASE IF NOT EXISTS vault
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vault;

-- ── Genres ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS genres (
                                      id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
                                      name        VARCHAR(100)    NOT NULL,
                                      created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                      PRIMARY KEY (id),
                                      UNIQUE KEY uq_genre_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Platforms ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS platforms (
                                         id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
                                         name        VARCHAR(100)    NOT NULL,
                                         created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                         PRIMARY KEY (id),
                                         UNIQUE KEY uq_platform_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Games ────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS games (
                                     id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
                                     title         VARCHAR(255)    NOT NULL,
                                     description   TEXT,
                                     genre_id      INT UNSIGNED,
                                     platform_id   INT UNSIGNED,
                                     release_year  SMALLINT UNSIGNED,
                                     rating        DECIMAL(3,1)    CHECK (rating BETWEEN 0 AND 10),
                                     cover_url     VARCHAR(500),
                                     rawg_id       INT UNSIGNED,                         -- links to RAWG API
                                     created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                     updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,
                                     PRIMARY KEY (id),
                                     KEY idx_genre    (genre_id),
                                     KEY idx_platform (platform_id),
                                     CONSTRAINT fk_games_genre    FOREIGN KEY (genre_id)
                                         REFERENCES genres(id)    ON DELETE SET NULL ON UPDATE CASCADE,
                                     CONSTRAINT fk_games_platform FOREIGN KEY (platform_id)
                                         REFERENCES platforms(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Seed data ────────────────────────────────────────────────
INSERT IGNORE INTO genres (name) VALUES
                                     ('Action'),('Adventure'),('RPG'),('Strategy'),
                                     ('Simulation'),('Sports'),('Horror'),('Puzzle');

INSERT IGNORE INTO platforms (name) VALUES
                                        ('PC'),('PlayStation 5'),('Xbox Series X'),
                                        ('Nintendo Switch'),('PlayStation 4'),('Xbox One');

INSERT IGNORE INTO games
(title, description, genre_id, platform_id, release_year, rating, cover_url)
VALUES
    ('Hollow Knight',
     'A challenging action-adventure set in a vast underground kingdom of bugs.',
     1, 1, 2017, 9.2,
     'https://images.igdb.com/igdb/image/upload/t_cover_big/co1rgi.jpg'),

    ('Stardew Valley',
     'Build the farm of your dreams in this relaxing simulation RPG.',
     5, 1, 2016, 9.0,
     'https://images.igdb.com/igdb/image/upload/t_cover_big/xrpmydnu9rpxvxfjkiu7.jpg'),

    ('Elden Ring',
     'An open-world action RPG forged by FromSoftware and George R.R. Martin.',
     3, 2, 2022, 9.5,
     'https://images.igdb.com/igdb/image/upload/t_cover_big/co4jni.jpg'),

    ('Hades',
     'Defy the god of death himself in this rogue-like dungeon crawler.',
     1, 1, 2020, 9.3, NULL),

    ('Minecraft',
     'Explore, craft, and survive in an infinite procedurally generated world.',
     5, 1, 2011, 8.8, NULL);