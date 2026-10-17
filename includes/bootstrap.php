<?php
require_once __DIR__ . '/db.php';

// XAMPP enables mbstring by default, these keep things working on a bare PHP build too.
if (!function_exists('mb_strlen')) { function mb_strlen($s) { return preg_match_all('/./su', (string)$s); } }
if (!function_exists('mb_substr')) { function mb_substr($s, $start, $len = null) { return implode('', array_slice(preg_split('//u', (string)$s, -1, PREG_SPLIT_NO_EMPTY), $start, $len)); } }

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

/* ---------- output helpers ---------- */
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function money($n): string { return CURRENCY . number_format((float)$n, 2); }
function redirect(string $url): never { header('Location: ' . $url); exit; }
function json_out($data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function poster_src(array $m): string {
    return !empty($m['poster_url']) ? $m['poster_url'] : 'api/poster.php?id=' . (int)$m['id'];
}

/* ---------- flash messages ---------- */
function flash(string $msg, string $type = 'ok'): void { $_SESSION['flash'] = [$msg, $type]; }
function take_flash(): ?array { $f = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); return $f; }

/* ---------- CSRF ---------- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_ok(): bool {
    $sent = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($sent) && hash_equals(csrf_token(), $sent);
}
function csrf_require(): void {
    if (!csrf_ok()) { http_response_code(419); exit('Session expired. Go back, reload the page and try again.'); }
}

/* ---------- auth ---------- */
function current_user(): ?array {
    static $user = false;
    if ($user !== false) return $user;
    $user = null;
    if (!empty($_SESSION['uid'])) {
        $st = db()->prepare('SELECT id, name, email, role FROM users WHERE id = ?');
        $st->execute([$_SESSION['uid']]);
        $user = $st->fetch() ?: null;
        if (!$user) unset($_SESSION['uid']);
    }
    return $user;
}
function login_user(int $id): void { session_regenerate_id(true); $_SESSION['uid'] = $id; }
function require_login(): array {
    $u = current_user();
    if (!$u) {
        $next = basename($_SERVER['SCRIPT_NAME']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
        redirect('login.php?next=' . urlencode($next));
    }
    return $u;
}
function require_admin(): array {
    $u = require_login();
    if ($u['role'] !== 'admin') { http_response_code(403); exit('Admins only.'); }
    return $u;
}
/** Only allow redirects to our own top-level pages. */
function safe_next($next): string {
    $next = is_string($next) ? $next : '';
    return preg_match('~^[a-z_]+\.(php|html)(\?[A-Za-z0-9=&_%.-]*)?$~', $next) ? $next : 'index.html';
}

/* ---------- shop helpers ---------- */
function owned_ids(int $uid): array {
    $st = db()->prepare("SELECT DISTINCT oi.movie_id FROM order_items oi JOIN orders o ON o.id = oi.order_id
                         WHERE o.user_id = ? AND o.status = 'paid' AND oi.movie_id IS NOT NULL");
    $st->execute([$uid]);
    return array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
}
function cart_count(?int $uid): int {
    if (!$uid) return 0;
    $st = db()->prepare('SELECT COUNT(*) FROM cart_items WHERE user_id = ?');
    $st->execute([$uid]);
    return (int)$st->fetchColumn();
}
function slugify(string $s): string {
    $s = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $s), '-'));
    return $s !== '' ? $s : 'genre';
}
