-- STREET REEL schema + seed data (MySQL / MariaDB)
-- phpMyAdmin: create a database named `streetreel` (utf8mb4_general_ci), select it, then Import this file.
-- Or skip all that and just open http://localhost/streetreel/install.php

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS genres (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE,
  slug VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS movies (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(120) NOT NULL,
  tagline VARCHAR(160) NOT NULL DEFAULT '',
  synopsis TEXT NOT NULL,
  release_year SMALLINT UNSIGNED NOT NULL,
  runtime_min SMALLINT UNSIGNED NOT NULL,
  rating VARCHAR(8) NOT NULL DEFAULT 'R',
  director VARCHAR(80) NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  genre_id INT UNSIGNED NULL,
  poster_url VARCHAR(255) NULL,
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_movies_genre (genre_id),
  INDEX idx_movies_featured (featured),
  CONSTRAINT fk_movies_genre FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cart_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  movie_id INT UNSIGNED NOT NULL,
  added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cart (user_id, movie_id),
  CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_cart_movie FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS orders (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(30) NOT NULL,
  status ENUM('paid','pending','refunded') NOT NULL DEFAULT 'paid',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_orders_user (user_id),
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id INT UNSIGNED NOT NULL,
  movie_id INT UNSIGNED NULL,
  title_snapshot VARCHAR(120) NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  INDEX idx_oi_movie (movie_id),
  CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_oi_movie FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO genres (name, slug) VALUES
('Street Fighting','street-fighting'),
('Martial Arts','martial-arts'),
('Gang Wars','gang-wars'),
('Revenge','revenge'),
('Underground Racing','underground-racing'),
('Tournaments','tournaments'),
('Dystopian','dystopian'),
('Grindhouse Classics','grindhouse-classics');

INSERT INTO movies (title, tagline, synopsis, release_year, runtime_min, rating, director, price, genre_id, featured) VALUES
('Concrete Knuckles','Eleven floors. One way out.','An ex-bouncer fights his way up eleven floors of an abandoned parking structure, where every level has a new champion and a new set of rules.',2024,104,'R','Mara Voss',12.99,(SELECT id FROM genres WHERE slug='street-fighting'),1),
('Basement Rules','No ref. No bell.','Rent is due, so Marisol steps into a basement ring where the only rule is that nobody calls a referee.',2019,96,'R','Dez Okafor',9.99,(SELECT id FROM genres WHERE slug='street-fighting'),0),
('Iron Lotus Alley','Behind the laundromat, the last dojo.','A dojo hidden behind a laundromat is the last thing standing between a neighborhood and the crew that wants it gone.',2021,112,'R','Kenji Arata',11.99,(SELECT id FROM genres WHERE slug='martial-arts'),1),
('Palm and Pavement','Learn it on the rooftops.','A courier with a broken wrist and a sharp memory learns the old forms from a street vendor, one rooftop at a time.',2016,99,'PG-13','Lin Mei Cho',8.99,(SELECT id FROM genres WHERE slug='martial-arts'),0),
('The Crane Lane Tournament','Sixteen in. One out.','Sixteen fighters, one condemned tram depot, and a purse that nobody can quite explain.',2013,118,'R','Tomas Heller',9.99,(SELECT id FROM genres WHERE slug='tournaments'),0),
('Eight Rounds to Rust','She came back under another name.','A retired champion enters a tournament under a fake name to find out who sold her last fight.',2022,107,'R','Nia Calder',11.99,(SELECT id FROM genres WHERE slug='tournaments'),0),
('Sixth Ward','Friday is coming.','Two crews share one block and one truce. The truce expires Friday.',2018,121,'R','Ray Delacroix',10.99,(SELECT id FROM genres WHERE slug='gang-wars'),0),
('Ash and Gasoline','The list is short. The night is long.','After the fire at the depot, one woman has a list, a jerry can, and nothing left to lose.',2023,109,'R','Mara Voss',12.99,(SELECT id FROM genres WHERE slug='revenge'),1),
('Debt Collector of Pier Nine','Every debt gets collected.','A quiet man collects debts for the harbor syndicate until the day his own name is on the ledger.',2020,101,'R','Silas Brandt',9.99,(SELECT id FROM genres WHERE slug='revenge'),0),
('Redline Rats','Built in a tunnel. Raced in the dark.','A crew of teenage mechanics builds a car in a drainage tunnel and takes on the city after dark.',2022,98,'PG-13','Jo Park',10.99,(SELECT id FROM genres WHERE slug='underground-racing'),0),
('Midnight Clutch','The clutch will not make it.','Eighty miles of closed highway, one clutch that is going to fail, and a rival with nothing to prove.',1992,94,'R','Hank Moreau',6.99,(SELECT id FROM genres WHERE slug='underground-racing'),0),
('Neon Gutter','Daylight costs extra.','In a city that taxes daylight, an unlicensed brawler becomes the face of a rebellion she never wanted.',2025,114,'R','Ines Kowalski',13.99,(SELECT id FROM genres WHERE slug='dystopian'),1),
('Wire Fence Republic','The wall is the easy part.','Walls go up around the old district and the people inside decide to write their own rules.',2017,126,'R','Tomas Heller',8.99,(SELECT id FROM genres WHERE slug='dystopian'),0),
('Switchblade Sundays','Sunday never ends.','A knife-fighting gang, a corrupt cop, and one very long Sunday in 1984.',1984,91,'R','Vic Santangelo',5.99,(SELECT id FROM genres WHERE slug='grindhouse-classics'),0),
('Dragon Gym Dropouts','Failing grade. Perfect kick.','Three dropouts, one failing kung fu school, and a regional championship they were never supposed to enter.',1987,88,'PG-13','Wei Lun Zhao',5.99,(SELECT id FROM genres WHERE slug='grindhouse-classics'),0);
