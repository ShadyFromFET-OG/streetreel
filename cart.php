<?php
require __DIR__ . '/includes/layout.php';
$u = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $mid = (int)($_POST['remove'] ?? 0);
    if ($mid) db()->prepare('DELETE FROM cart_items WHERE user_id = ? AND movie_id = ?')->execute([$u['id'], $mid]);
    redirect('cart.php');
}

$st = db()->prepare('SELECT m.id, m.title, m.release_year, m.price, m.poster_url, g.name AS genre
                     FROM cart_items c JOIN movies m ON m.id = c.movie_id LEFT JOIN genres g ON g.id = m.genre_id
                     WHERE c.user_id = ? ORDER BY c.id DESC');
$st->execute([$u['id']]);
$items = $st->fetchAll();
$total = array_sum(array_map(fn($i) => (float)$i['price'], $items));

page_start('Your cart');
echo h1('Your cart');
?>
<?php if (!$items): ?>
  <div class="mt-10 border-4 border-dashed border-paper/30 p-12 text-center">
    <p class="font-display font-black uppercase text-4xl">Nothing in your cart</p>
    <p class="mt-2 text-paper/80">Pick a film off the wall and it will show up here.</p>
    <a href="index.html#wall" class="inline-block mt-6 bg-blood font-stencil uppercase px-6 py-3 slab press">Browse the wall</a>
  </div>
<?php else: ?>
<div class="mt-10 grid lg:grid-cols-12 gap-10">
  <ul class="lg:col-span-8 divide-y-2 divide-paper/20 border-y-2 border-paper/20">
    <?php foreach ($items as $i): ?>
      <li class="flex items-center gap-4 py-4">
        <a href="movie.php?id=<?= (int)$i['id'] ?>"><img src="<?= e(poster_src($i)) ?>" alt="" width="64" height="96" class="w-16 aspect-[2/3] object-cover border-2 border-paper/30"></a>
        <div class="flex-1 min-w-0">
          <a href="movie.php?id=<?= (int)$i['id'] ?>" class="font-display font-extrabold uppercase text-2xl leading-none hover:text-hazard"><?= e($i['title']) ?></a>
          <p class="font-mono text-sm text-paper/70 mt-1"><?= (int)$i['release_year'] ?>, <?= e($i['genre'] ?? 'Unsorted') ?></p>
        </div>
        <span class="font-mono font-bold text-lg"><?= money($i['price']) ?></span>
        <form method="post"><?= csrf_field() ?><button name="remove" value="<?= (int)$i['id'] ?>" aria-label="Remove <?= e($i['title']) ?> from cart" class="font-stencil uppercase text-sm border-2 border-paper/50 px-3 py-1 hover:border-blood hover:text-blood">Remove</button></form>
      </li>
    <?php endforeach; ?>
  </ul>
  <aside class="lg:col-span-4">
    <div class="paper-sheet torn-b p-6 pb-10 rotate-1">
      <p class="font-mono text-sm"><?= count($items) ?> film<?= count($items) === 1 ? '' : 's' ?></p>
      <p class="font-display font-black text-6xl leading-none mt-1"><?= money($total) ?></p>
      <a href="checkout.php" class="mt-5 inline-block bg-blood text-paper font-stencil uppercase px-6 py-3 slab press">Check out</a>
    </div>
  </aside>
</div>
<?php endif; ?>
<?php page_end();
