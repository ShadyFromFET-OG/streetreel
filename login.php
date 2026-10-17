<?php
require __DIR__ . '/includes/layout.php';

if (current_user()) redirect('index.html');
$next  = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $email = trim((string)($_POST['email'] ?? ''));
    $pass  = (string)($_POST['password'] ?? '');
    $st = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
    $st->execute([$email]);
    $row = $st->fetch();
    // unknown email: burn the same hashing time so response speed doesn't reveal which emails exist
    $hash = $row['password_hash'] ?? password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
    if (password_verify($pass, $hash) && $row) {
        login_user((int)$row['id']);
        redirect($next);
    }
    $error = 'Wrong email or password.';
}

page_start('Log in', 'max-w-xl');
echo h1('Log in');
?>
<form method="post" class="mt-8 space-y-5 bg-charcoal border-2 border-paper/40 p-6 slab" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="next" value="<?= e($next) ?>">
  <?php if ($error): ?><p class="bg-blood text-paper font-mono font-bold p-3" role="alert"><?= e($error) ?></p><?php endif; ?>
  <label class="block"><span class="font-mono text-sm">Email</span>
    <input name="email" type="email" required autocomplete="email" value="<?= e($email) ?>" class="mt-1 w-full bg-asphalt border-2 border-paper/60 px-4 py-3 font-mono focus:border-hazard"></label>
  <label class="block"><span class="font-mono text-sm">Password</span>
    <input name="password" type="password" required autocomplete="current-password" class="mt-1 w-full bg-asphalt border-2 border-paper/60 px-4 py-3 font-mono focus:border-hazard"></label>
  <button class="bg-blood font-stencil uppercase text-lg px-6 py-3 slab press">Log in</button>
  <p class="font-mono text-sm text-paper/70">No account yet? <a class="text-hazard underline" href="register.php?next=<?= urlencode($next) ?>">Sign up</a></p>
</form>
<?php page_end();
