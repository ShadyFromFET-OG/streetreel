<?php
require __DIR__ . '/includes/layout.php';

if (current_user()) redirect('index.html');
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? '');
$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $name    = trim((string)($_POST['name'] ?? ''));
    $email   = trim((string)($_POST['email'] ?? ''));
    $pass    = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm'] ?? '');

    if (mb_strlen($name) < 2 || mb_strlen($name) > 60)       $errors[] = 'Name needs 2 to 60 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if (strlen($pass) < 8)                                   $errors[] = 'Password needs at least 8 characters.';
    if ($pass !== $confirm)                                  $errors[] = 'The two passwords do not match.';

    if (!$errors) {
        try {
            db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)')
                ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            login_user((int)db()->lastInsertId());
            flash('Welcome to the wall, ' . $name . '.');
            redirect($next);
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') $errors[] = 'That email already has an account. Log in instead.';
            else throw $ex;
        }
    }
}

page_start('Sign up', 'max-w-xl');
echo h1('Sign up');
?>
<form method="post" class="mt-8 space-y-5 bg-charcoal border-2 border-paper/40 p-6 slab" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="next" value="<?= e($next) ?>">
  <?php if ($errors): ?>
    <div class="bg-blood text-paper font-mono font-bold p-3" role="alert"><?php foreach ($errors as $er): ?><p><?= e($er) ?></p><?php endforeach; ?></div>
  <?php endif; ?>
  <label class="block"><span class="font-mono text-sm">Name</span>
    <input name="name" required maxlength="60" autocomplete="name" value="<?= e($name) ?>" class="mt-1 w-full bg-asphalt border-2 border-paper/60 px-4 py-3 font-mono focus:border-hazard"></label>
  <label class="block"><span class="font-mono text-sm">Email</span>
    <input name="email" type="email" required autocomplete="email" value="<?= e($email) ?>" class="mt-1 w-full bg-asphalt border-2 border-paper/60 px-4 py-3 font-mono focus:border-hazard"></label>
  <label class="block"><span class="font-mono text-sm">Password (8 characters or more)</span>
    <input name="password" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full bg-asphalt border-2 border-paper/60 px-4 py-3 font-mono focus:border-hazard"></label>
  <label class="block"><span class="font-mono text-sm">Repeat password</span>
    <input name="confirm" type="password" required minlength="8" autocomplete="new-password" class="mt-1 w-full bg-asphalt border-2 border-paper/60 px-4 py-3 font-mono focus:border-hazard"></label>
  <button class="bg-blood font-stencil uppercase text-lg px-6 py-3 slab press">Create account</button>
  <p class="font-mono text-sm text-paper/70">Already in? <a class="text-hazard underline" href="login.php?next=<?= urlencode($next) ?>">Log in</a></p>
</form>
<?php page_end();
