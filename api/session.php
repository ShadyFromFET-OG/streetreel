<?php
// Who is logged in, cart size, CSRF token, brand settings. index.html calls this on load.
require __DIR__ . '/../includes/bootstrap.php';
$u = current_user();
json_out([
    'user'     => $u ? ['name' => $u['name'], 'role' => $u['role']] : null,
    'cart'     => cart_count($u['id'] ?? null),
    'csrf'     => csrf_token(),
    'brand'    => BRAND_NAME,
    'tagline'  => BRAND_TAGLINE,
    'currency' => CURRENCY,
]);
