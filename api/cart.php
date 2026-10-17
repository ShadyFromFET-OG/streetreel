<?php
// POST JSON {action:"add"|"remove", movie_id:int}  + header X-CSRF-Token
require __DIR__ . '/../includes/bootstrap.php';

$u = current_user();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_out(['count' => cart_count($u['id'] ?? null)]);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['error' => 'Method not allowed.'], 405);
if (!csrf_ok())  json_out(['error' => 'Session expired. Reload the page.'], 419);
if (!$u)         json_out(['error' => 'Log in to use the cart.', 'login' => 'login.php'], 401);

$body    = json_decode(file_get_contents('php://input'), true) ?: [];
$action  = $body['action'] ?? '';
$movieId = (int)($body['movie_id'] ?? 0);

$st = db()->prepare('SELECT id, title FROM movies WHERE id = ?');
$st->execute([$movieId]);
$movie = $st->fetch();
if (!$movie) json_out(['error' => 'That movie does not exist.'], 404);

if ($action === 'add') {
    if (in_array($movieId, owned_ids($u['id']), true)) {
        json_out(['error' => 'You already own this one.', 'count' => cart_count($u['id'])], 409);
    }
    db()->prepare('INSERT IGNORE INTO cart_items (user_id, movie_id) VALUES (?, ?)')->execute([$u['id'], $movieId]);
    json_out(['count' => cart_count($u['id']), 'message' => $movie['title'] . ' is in your cart.']);
}
if ($action === 'remove') {
    db()->prepare('DELETE FROM cart_items WHERE user_id = ? AND movie_id = ?')->execute([$u['id'], $movieId]);
    json_out(['count' => cart_count($u['id']), 'message' => 'Removed from cart.']);
}
json_out(['error' => 'Unknown action.'], 400);
