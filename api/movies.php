<?php
// GET api/movies.php?q=&genre=&sort=new|year|old|price|title&featured=1&limit=60
require __DIR__ . '/../includes/bootstrap.php';

$q        = trim((string)($_GET['q'] ?? ''));
$genre    = trim((string)($_GET['genre'] ?? ''));
$sort     = (string)($_GET['sort'] ?? 'new');
$featured = !empty($_GET['featured']);
$limit    = max(1, min(60, (int)($_GET['limit'] ?? 60)));

$where = []; $args = [];
if ($q !== '') {
    $like = '%' . addcslashes(mb_substr($q, 0, 60), '%_\\') . '%';
    $where[] = '(m.title LIKE ? OR m.director LIKE ? OR m.tagline LIKE ?)';
    array_push($args, $like, $like, $like);
}
if ($genre !== '') { $where[] = 'g.slug = ?'; $args[] = $genre; }
if ($featured)     { $where[] = 'm.featured = 1'; }

$orders = [
    'new'   => 'm.created_at DESC, m.id DESC',
    'year'  => 'm.release_year DESC, m.id DESC',
    'old'   => 'm.release_year ASC, m.id ASC',
    'price' => 'm.price ASC, m.id ASC',
    'title' => 'm.title ASC',
];
$order = $orders[$sort] ?? $orders['new'];

$sql = 'SELECT m.id, m.title, m.tagline, m.release_year, m.runtime_min, m.rating, m.director, m.price,
               m.poster_url, m.featured, g.name AS genre, g.slug AS genre_slug
        FROM movies m LEFT JOIN genres g ON g.id = m.genre_id'
     . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
     . ' ORDER BY ' . $order . ' LIMIT ' . $limit;
$st = db()->prepare($sql);
$st->execute($args);

$movies = array_map(fn($m) => [
    'id'       => (int)$m['id'],
    'title'    => $m['title'],
    'tagline'  => $m['tagline'],
    'year'     => (int)$m['release_year'],
    'runtime'  => (int)$m['runtime_min'],
    'rating'   => $m['rating'],
    'director' => $m['director'],
    'price'    => (float)$m['price'],
    'genre'    => $m['genre'],
    'genreSlug'=> $m['genre_slug'],
    'featured' => (bool)$m['featured'],
    'poster'   => poster_src($m),
], $st->fetchAll());

$genres = db()->query('SELECT g.name, g.slug, COUNT(m.id) AS total
                       FROM genres g LEFT JOIN movies m ON m.genre_id = g.id
                       GROUP BY g.id ORDER BY g.name')->fetchAll();

json_out(['movies' => $movies, 'genres' => $genres]);
