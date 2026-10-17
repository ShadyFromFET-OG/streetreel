<?php
/* One-time installer. Creates the database, tables, sample movies and your admin account.
   DELETE THIS FILE when you're done. */
require __DIR__ . '/includes/config.php';

function out(string $title, string $body, bool $ok = true): never {
    ?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Install | <?= htmlspecialchars(BRAND_NAME) ?></title>
<style>body{background:#101010;color:#E8E0D0;font-family:system-ui,sans-serif;max-width:560px;margin:48px auto;padding:0 20px}
h1{font-family:Impact,sans-serif;text-transform:uppercase;font-size:44px;margin:0 0 16px}
.box{border:2px solid <?= $ok ? '#F5D547' : '#E32636' ?>;padding:20px}
input{display:block;width:100%;box-sizing:border-box;margin:6px 0 16px;padding:10px;background:#1B1B1B;color:#E8E0D0;border:2px solid #888;font-size:16px}
button{background:#E32636;color:#E8E0D0;border:0;padding:12px 24px;font-size:16px;text-transform:uppercase;cursor:pointer}
code{background:#292929;padding:2px 6px}a{color:#F5D547}</style></head><body>
<h1><?= htmlspecialchars($title) ?></h1><div class="box"><?= $body ?></div></body></html><?php
    exit;
}

try {
    $root = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    out('MySQL not reachable', '<p>Start <b>MySQL</b> in the XAMPP Control Panel and check <code>includes/config.php</code>.</p>', false);
}

// already installed?
$dbName = str_replace('`', '', DB_NAME);
$exists = $root->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = " . $root->quote($dbName))->fetchColumn();
if ($exists) {
    $root->exec("USE `$dbName`");
    $has = $root->query("SHOW TABLES LIKE 'users'")->fetchColumn();
    if ($has && (int)$root->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn() > 0) {
        out('Already installed', '<p>An admin account exists. Delete <code>install.php</code> and open <a href="index.html">the site</a>.</p>');
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = (string)($_POST['password'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8) {
        $error = 'Enter a name, a valid email and a password of 8+ characters.';
    } else {
        try {
            $root->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $root->exec("USE `$dbName`");
            $sql = file_get_contents(__DIR__ . '/database/schema.sql');
            $sql = preg_replace('~^--.*$~m', '', $sql);
            $alreadySeeded = $root->query("SHOW TABLES LIKE 'movies'")->fetchColumn()
                          && (int)$root->query('SELECT COUNT(*) FROM movies')->fetchColumn() > 0;
            foreach (preg_split('~;\s*\n~', $sql) as $stmt) {
                $stmt = trim($stmt);
                if ($stmt === '') continue;
                if ($alreadySeeded && stripos($stmt, 'INSERT INTO movies') === 0) continue;
                $root->exec($stmt);
            }
            $root->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')")
                 ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            out('Installed', '<p>Database <code>' . htmlspecialchars($dbName) . '</code> is ready with sample movies.</p>
                <p><b>Delete <code>install.php</code> now.</b></p>
                <p><a href="index.html">Open the site</a> or <a href="login.php">log in as admin</a>.</p>');
        } catch (Throwable $e) {
            $error = 'Install failed: ' . $e->getMessage();
        }
    }
}

out('Install ' . BRAND_NAME, '
<form method="post">' . ($error ? '<p style="color:#E32636;font-weight:bold">' . htmlspecialchars($error) . '</p>' : '') . '
<p>Creates the <code>' . htmlspecialchars($dbName) . '</code> database, the tables, 15 sample movies and your admin login.</p>
<label>Admin name<input name="name" required></label>
<label>Admin email<input name="email" type="email" required></label>
<label>Admin password (8+ characters)<input name="password" type="password" minlength="8" required></label>
<button>Install</button></form>');
