<?php
require __DIR__ . '/includes/layout.php';
$admin = require_admin();
$pdo = db();

$ratings = ['G', 'PG', 'PG-13', 'R', 'NC-17', 'NR'];
$errors = [];
$tab = ($_GET['tab'] ?? 'movies') === 'orders' ? 'orders' : 'movies';

/* ---------- actions ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_movie') {
        $pdo->prepare('DELETE FROM movies WHERE id = ?')->execute([(int)($_POST['id'] ?? 0)]);
        flash('Movie deleted.');
        redirect('admin.php');
    }

    if ($action === 'add_genre') {
        $gname = trim((string)($_POST['genre_name'] ?? ''));
        if ($gname === '' || mb_strlen($gname) > 60) {
            flash('Genre name needs 1 to 60 characters.', 'err');
        } else {
            $pdo->prepare('INSERT IGNORE INTO genres (name, slug) VALUES (?, ?)')->execute([$gname, slugify($gname)]);
            flash('Genre saved.');
        }
        redirect('admin.php');
    }

    if ($action === 'save_movie') {
        $mid      = (int)($_POST['id'] ?? 0);
        $f = [
            'title'        => trim((string)($_POST['title'] ?? '')),
            'tagline'      => trim((string)($_POST['tagline'] ?? '')),
            'synopsis'     => trim((string)($_POST['synopsis'] ?? '')),
            'release_year' => (int)($_POST['release_year'] ?? 0),
            'runtime_min'  => (int)($_POST['runtime_min'] ?? 0),
            'rating'       => (string)($_POST['rating'] ?? ''),
            'director'     => trim((string)($_POST['director'] ?? '')),
            'price'        => (string)($_POST['price'] ?? ''),
            'genre_id'     => (int)($_POST['genre_id'] ?? 0),
            'poster_url'   => trim((string)($_POST['poster_url'] ?? '')),
            'featured'     => isset($_POST['featured']) ? 1 : 0,
        ];
        if ($f['title'] === '' || mb_strlen($f['title']) > 120)      $errors[] = 'Title is required (max 120 characters).';
        if (mb_strlen($f['tagline']) > 160)                          $errors[] = 'Tagline is max 160 characters.';
        if ($f['synopsis'] === '')                                   $errors[] = 'Synopsis is required.';
        if ($f['release_year'] < 1900 || $f['release_year'] > 2100)  $errors[] = 'Release year must be between 1900 and 2100.';
        if ($f['runtime_min'] < 1 || $f['runtime_min'] > 600)        $errors[] = 'Runtime must be 1 to 600 minutes.';
        if (!in_array($f['rating'], $ratings, true))                 $errors[] = 'Pick a rating.';
        if ($f['director'] === '' || mb_strlen($f['director']) > 80) $errors[] = 'Director is required (max 80 characters).';
        if (!is_numeric($f['price']) || $f['price'] < 0 || $f['price'] > 9999) $errors[] = 'Price must be a number from 0 to 9999.';
        if ($f['poster_url'] !== '' && (!preg_match('~^https?://~i', $f['poster_url']) || !filter_var($f['poster_url'], FILTER_VALIDATE_URL) || strlen($f['poster_url']) > 255))
            $errors[] = 'Poster URL must be a full http(s) link, or leave it empty to use the generated poster.';
        $gcheck = $pdo->prepare('SELECT 1 FROM genres WHERE id = ?'); $gcheck->execute([$f['genre_id']]);
        if (!$gcheck->fetchColumn()) $errors[] = 'Pick a genre.';

        if (!$errors) {
            $vals = [$f['title'], $f['tagline'], $f['synopsis'], $f['release_year'], $f['runtime_min'], $f['rating'],
                     $f['director'], number_format((float)$f['price'], 2, '.', ''), $f['genre_id'], $f['poster_url'] ?: null, $f['featured']];
            if ($mid) {
                $pdo->prepare('UPDATE movies SET title=?, tagline=?, synopsis=?, release_year=?, runtime_min=?, rating=?, director=?, price=?, genre_id=?, poster_url=?, featured=? WHERE id=?')
                    ->execute([...$vals, $mid]);
                flash('Movie updated.');
            } else {
                $pdo->prepare('INSERT INTO movies (title, tagline, synopsis, release_year, runtime_min, rating, director, price, genre_id, poster_url, featured) VALUES (?,?,?,?,?,?,?,?,?,?,?)')
                    ->execute($vals);
                flash('Movie added to the wall.');
            }
            redirect('admin.php');
        }
        $editing = $f + ['id' => $mid];   // keep what was typed
    }
}

/* ---------- data for the page ---------- */
$genres = $pdo->query('SELECT id, name FROM genres ORDER BY name')->fetchAll();
if (!isset($editing)) {
    $editing = ['id' => 0, 'title' => '', 'tagline' => '', 'synopsis' => '', 'release_year' => date('Y'), 'runtime_min' => 100,
                'rating' => 'R', 'director' => '', 'price' => '9.99', 'genre_id' => 0, 'poster_url' => '', 'featured' => 0];
    if (!empty($_GET['edit'])) {
        $st = $pdo->prepare('SELECT * FROM movies WHERE id = ?'); $st->execute([(int)$_GET['edit']]);
        if ($row = $st->fetch()) $editing = $row + ['poster_url' => ''];
        $editing['poster_url'] = $editing['poster_url'] ?? '';
    }
}
$movies = $pdo->query('SELECT m.id, m.title, m.release_year, m.price, m.featured, g.name AS genre FROM movies m LEFT JOIN genres g ON g.id = m.genre_id ORDER BY m.id DESC')->fetchAll();
$stats = [
    'Movies'  => (int)$pdo->query('SELECT COUNT(*) FROM movies')->fetchColumn(),
    'Members' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
    'Orders'  => (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status='paid'")->fetchColumn(),
    'Revenue' => money($pdo->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='paid'")->fetchColumn()),
];
$orders = $tab === 'orders'
    ? $pdo->query("SELECT o.id, o.total, o.payment_method, o.status, o.created_at, u.name, u.email FROM orders o JOIN users u ON u.id = o.user_id ORDER BY o.id DESC LIMIT 100")->fetchAll()
    : [];

$input = 'mt-1 w-full bg-asphalt border-2 border-paper/60 px-3 py-2 font-mono focus:border-hazard';
page_start('Admin');
echo h1('Admin');
?>
<dl class="mt-8 grid grid-cols-2 md:grid-cols-4 border-2 border-paper/40">
  <?php foreach ($stats as $label => $val): ?>
    <div class="p-4 border-b-2 md:border-b-0 border-r-2 last:border-r-0 border-paper/40"><dt class="font-mono text-sm text-paper/60"><?= e($label) ?></dt><dd class="font-display font-black text-4xl leading-none mt-1"><?= e($val) ?></dd></div>
  <?php endforeach; ?>
</dl>

<nav class="mt-8 flex gap-2 font-stencil uppercase text-sm" aria-label="Admin sections">
  <a href="admin.php" class="px-4 py-2 border-2 <?= $tab === 'movies' ? 'bg-blood border-blood' : 'border-paper/50 hover:border-hazard' ?>">Movies</a>
  <a href="admin.php?tab=orders" class="px-4 py-2 border-2 <?= $tab === 'orders' ? 'bg-blood border-blood' : 'border-paper/50 hover:border-hazard' ?>">Orders</a>
</nav>

<?php if ($tab === 'orders'): ?>
  <div class="mt-6 overflow-x-auto">
    <table class="w-full font-mono text-sm text-left border-2 border-paper/30">
      <thead class="bg-concrete"><tr><th class="p-3">Order</th><th class="p-3">Date</th><th class="p-3">Member</th><th class="p-3">Paid with</th><th class="p-3">Status</th><th class="p-3 text-right">Total</th></tr></thead>
      <tbody class="divide-y divide-paper/20">
      <?php foreach ($orders as $o): ?>
        <tr><td class="p-3 font-bold">#<?= (int)$o['id'] ?></td><td class="p-3 whitespace-nowrap"><?= e(date('M j, Y H:i', strtotime($o['created_at']))) ?></td><td class="p-3"><?= e($o['name']) ?> <span class="text-paper/60"><?= e($o['email']) ?></span></td><td class="p-3"><?= e($o['payment_method']) ?></td><td class="p-3"><?= e($o['status']) ?></td><td class="p-3 text-right font-bold"><?= money($o['total']) ?></td></tr>
      <?php endforeach; ?>
      <?php if (!$orders): ?><tr><td colspan="6" class="p-6 text-center text-paper/70">No orders yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
<?php else: ?>
<div class="mt-6 grid lg:grid-cols-12 gap-10">
  <section class="lg:col-span-7 overflow-x-auto">
    <table class="w-full font-mono text-sm text-left border-2 border-paper/30">
      <thead class="bg-concrete"><tr><th class="p-3">Title</th><th class="p-3">Genre</th><th class="p-3">Year</th><th class="p-3">Price</th><th class="p-3"><span class="sr-only">Actions</span></th></tr></thead>
      <tbody class="divide-y divide-paper/20">
      <?php foreach ($movies as $m): ?>
        <tr>
          <td class="p-3 font-bold"><?= e($m['title']) ?><?= $m['featured'] ? ' <span class="bg-hazard text-asphalt px-1 text-xs">hero</span>' : '' ?></td>
          <td class="p-3"><?= e($m['genre'] ?? '-') ?></td>
          <td class="p-3"><?= (int)$m['release_year'] ?></td>
          <td class="p-3"><?= money($m['price']) ?></td>
          <td class="p-3 whitespace-nowrap">
            <a href="admin.php?edit=<?= (int)$m['id'] ?>" class="underline hover:text-hazard">Edit</a>
            <form method="post" class="inline ml-3" onsubmit="return confirm('Delete <?= e(addslashes($m['title'])) ?>? This also clears it from carts.')">
              <?= csrf_field() ?><input type="hidden" name="action" value="delete_movie"><input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
              <button class="underline text-blood hover:text-paper">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <form method="post" class="mt-8 flex flex-wrap items-end gap-3">
      <?= csrf_field() ?><input type="hidden" name="action" value="add_genre">
      <label class="flex-1 min-w-[200px]"><span class="font-mono text-sm">New genre</span><input name="genre_name" maxlength="60" class="<?= $input ?>"></label>
      <button class="bg-concrete border-2 border-paper/50 font-stencil uppercase px-4 py-2 hover:border-hazard">Add genre</button>
    </form>
  </section>

  <section class="lg:col-span-5">
    <form method="post" class="bg-charcoal border-2 border-paper/40 p-6 slab space-y-4" id="movieForm">
      <h2 class="font-display font-black uppercase text-3xl leading-none"><?= $editing['id'] ? 'Edit movie' : 'Add a movie' ?></h2>
      <?= csrf_field() ?><input type="hidden" name="action" value="save_movie"><input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
      <?php if ($errors): ?><div class="bg-blood text-paper font-mono font-bold p-3" role="alert"><?php foreach ($errors as $er): ?><p><?= e($er) ?></p><?php endforeach; ?></div><?php endif; ?>
      <label class="block"><span class="font-mono text-sm">Title</span><input name="title" required maxlength="120" value="<?= e($editing['title']) ?>" class="<?= $input ?>"></label>
      <label class="block"><span class="font-mono text-sm">Tagline</span><input name="tagline" maxlength="160" value="<?= e($editing['tagline']) ?>" class="<?= $input ?>"></label>
      <label class="block"><span class="font-mono text-sm">Synopsis</span><textarea name="synopsis" required rows="4" class="<?= $input ?>"><?= e($editing['synopsis']) ?></textarea></label>
      <div class="grid grid-cols-3 gap-3">
        <label class="block"><span class="font-mono text-sm">Year</span><input name="release_year" type="number" min="1900" max="2100" required value="<?= e($editing['release_year']) ?>" class="<?= $input ?>"></label>
        <label class="block"><span class="font-mono text-sm">Minutes</span><input name="runtime_min" type="number" min="1" max="600" required value="<?= e($editing['runtime_min']) ?>" class="<?= $input ?>"></label>
        <label class="block"><span class="font-mono text-sm">Price</span><input name="price" type="number" step="0.01" min="0" max="9999" required value="<?= e($editing['price']) ?>" class="<?= $input ?>"></label>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <label class="block"><span class="font-mono text-sm">Genre</span>
          <select name="genre_id" required class="<?= $input ?>"><option value="">Pick one</option>
            <?php foreach ($genres as $g): ?><option value="<?= (int)$g['id'] ?>" <?= (int)$editing['genre_id'] === (int)$g['id'] ? 'selected' : '' ?>><?= e($g['name']) ?></option><?php endforeach; ?>
          </select></label>
        <label class="block"><span class="font-mono text-sm">Rating</span>
          <select name="rating" class="<?= $input ?>"><?php foreach ($ratings as $r): ?><option <?= $editing['rating'] === $r ? 'selected' : '' ?>><?= e($r) ?></option><?php endforeach; ?></select></label>
      </div>
      <label class="block"><span class="font-mono text-sm">Director</span><input name="director" required maxlength="80" value="<?= e($editing['director']) ?>" class="<?= $input ?>"></label>
      <label class="block"><span class="font-mono text-sm">Poster URL (leave empty for the generated poster)</span><input name="poster_url" type="url" maxlength="255" value="<?= e($editing['poster_url']) ?>" class="<?= $input ?>"></label>
      <label class="flex items-center gap-3 font-mono text-sm"><input type="checkbox" name="featured" value="1" <?= !empty($editing['featured']) ? 'checked' : '' ?> class="accent-[#E32636] w-4 h-4"> Show in the homepage hero</label>
      <div class="flex gap-3">
        <button class="bg-blood font-stencil uppercase px-5 py-2 slab-sm press"><?= $editing['id'] ? 'Save changes' : 'Add movie' ?></button>
        <?php if ($editing['id']): ?><a href="admin.php" class="font-stencil uppercase px-5 py-2 border-2 border-paper/50 hover:border-hazard">Cancel</a><?php endif; ?>
      </div>
    </form>
  </section>
</div>
<?php endif; ?>
<?php page_end();
