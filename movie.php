<?php
require __DIR__ . '/includes/layout.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT m.*, g.name AS genre, g.slug AS genre_slug FROM movies m LEFT JOIN genres g ON g.id = m.genre_id WHERE m.id = ?');
$st->execute([$id]);
$m = $st->fetch();

if (!$m) {
    http_response_code(404);
    page_start('Not found');
    echo h1('Reel not found');
    echo '<p class="mt-4 text-lg text-paper/80">That movie was pulled off the wall or never existed.</p>';
    echo '<a href="index.html#wall" class="inline-block mt-6 bg-blood font-stencil uppercase px-6 py-3 slab press">Back to the wall</a>';
    page_end();
    exit;
}

$u = current_user();
$owned = $u ? in_array($id, owned_ids($u['id']), true) : false;
$inCart = false;
if ($u && !$owned) {
    $c = db()->prepare('SELECT 1 FROM cart_items WHERE user_id = ? AND movie_id = ?');
    $c->execute([$u['id'], $id]);
    $inCart = (bool)$c->fetchColumn();
}

$rel = db()->prepare('SELECT id, title, release_year, price, poster_url FROM movies WHERE genre_id <=> ? AND id <> ? ORDER BY release_year DESC LIMIT 4');
$rel->execute([$m['genre_id'], $id]);
$related = $rel->fetchAll();

page_start($m['title']);
?>
<div class="grid lg:grid-cols-12 gap-12">
  <div class="lg:col-span-5">
    <div class="relative tilt max-w-md mx-auto lg:mx-0" style="--r:-2deg">
      <span class="tape" style="left:50%;top:-14px;margin-left:-42px;--tr:4deg"></span>
      <img src="<?= e(poster_src($m)) ?>" alt="Poster for <?= e($m['title']) ?>" width="600" height="900" class="w-full aspect-[2/3] object-cover border-2 border-paper/30 shadow-[10px_10px_0_#000]">
    </div>
  </div>

  <div class="lg:col-span-7">
    <?php if ($m['genre']): ?>
      <a href="index.html" class="inline-block font-stencil uppercase text-sm border-2 border-paper/60 px-3 py-1 hover:border-hazard mb-5"><?= e($m['genre']) ?></a>
    <?php endif; ?>
    <?= h1($m['title'], 'text-6xl sm:text-8xl') ?>
    <?php if ($m['tagline']): ?><p class="font-marker text-hazard text-2xl mt-5 -rotate-1"><?= e($m['tagline']) ?></p><?php endif; ?>

    <dl class="mt-8 grid grid-cols-2 sm:grid-cols-4 border-2 border-paper/40 font-mono text-sm">
      <div class="p-3 border-r-2 border-paper/40"><dt class="text-paper/60">Released</dt><dd class="font-bold text-base"><?= (int)$m['release_year'] ?></dd></div>
      <div class="p-3 sm:border-r-2 border-paper/40"><dt class="text-paper/60">Runtime</dt><dd class="font-bold text-base"><?= (int)$m['runtime_min'] ?> min</dd></div>
      <div class="p-3 border-t-2 sm:border-t-0 border-r-2 border-paper/40"><dt class="text-paper/60">Rated</dt><dd class="font-bold text-base"><?= e($m['rating']) ?></dd></div>
      <div class="p-3 border-t-2 sm:border-t-0 border-paper/40"><dt class="text-paper/60">Director</dt><dd class="font-bold text-base"><?= e($m['director']) ?></dd></div>
    </dl>

    <p class="mt-8 max-w-prose text-lg leading-relaxed text-paper/90"><?= e($m['synopsis']) ?></p>

    <div class="mt-10 paper-sheet torn-b p-6 pb-10 max-w-lg -rotate-1">
      <p class="font-display font-black text-6xl leading-none"><?= money($m['price']) ?></p>
      <p class="mt-2 font-mono text-sm">Digital access. Pay once and it stays in your library.</p>
      <div class="mt-5" id="buyBox">
        <?php if ($owned): ?>
          <a href="account.php" class="inline-block bg-asphalt text-paper font-stencil uppercase px-6 py-3">In your library</a>
        <?php elseif ($inCart): ?>
          <a href="cart.php" class="inline-block bg-asphalt text-paper font-stencil uppercase px-6 py-3 slab press">Go to cart</a>
        <?php else: ?>
          <button id="addBtn" type="button" class="bg-blood text-paper font-stencil uppercase px-6 py-3 slab press">Add to cart</button>
        <?php endif; ?>
        <p id="msg" class="mt-3 font-mono text-sm font-bold" role="status"></p>
      </div>
    </div>
  </div>
</div>

<?php if ($related): ?>
<section class="mt-24">
  <h2 class="font-display font-black uppercase text-4xl sm:text-5xl leading-none mb-8">More from <?= e($m['genre'] ?? 'the wall') ?></h2>
  <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
    <?php foreach ($related as $i => $r): ?>
      <a href="movie.php?id=<?= (int)$r['id'] ?>" class="block tilt" style="--r:<?= [-1.5, 1, -0.8, 1.6][$i % 4] ?>deg">
        <img src="<?= e(poster_src($r)) ?>" alt="Poster for <?= e($r['title']) ?>" loading="lazy" width="600" height="900" class="w-full aspect-[2/3] object-cover border-2 border-paper/25">
        <span class="block mt-2 font-display font-extrabold uppercase text-lg leading-none"><?= e($r['title']) ?></span>
        <span class="font-mono text-xs text-paper/70"><?= (int)$r['release_year'] ?>, <?= money($r['price']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<script>
const addBtn = document.getElementById('addBtn');
if (addBtn) addBtn.addEventListener('click', async () => {
  const msg = document.getElementById('msg');
  try {
    await reelCart('add', <?= (int)$id ?>);
    document.getElementById('buyBox').innerHTML = '<a href="cart.php" class="inline-block bg-asphalt text-paper font-stencil uppercase px-6 py-3 slab press">Go to cart</a><p class="mt-3 font-mono text-sm font-bold" role="status">Added to your cart.</p>';
  } catch (e) {
    if (e.status === 401) { location.href = 'login.php?next=' + encodeURIComponent('movie.php?id=<?= (int)$id ?>'); return; }
    msg.textContent = e.message;
  }
});
</script>
<?php page_end();
