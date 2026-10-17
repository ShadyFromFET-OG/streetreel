<?php
require __DIR__ . '/includes/layout.php';
$u = require_login();

$lib = db()->prepare("SELECT DISTINCT m.id, m.title, m.release_year, m.poster_url
                      FROM order_items oi JOIN orders o ON o.id = oi.order_id JOIN movies m ON m.id = oi.movie_id
                      WHERE o.user_id = ? AND o.status = 'paid' ORDER BY m.title");
$lib->execute([$u['id']]);
$library = $lib->fetchAll();

$od = db()->prepare("SELECT o.id, o.total, o.payment_method, o.created_at, GROUP_CONCAT(oi.title_snapshot ORDER BY oi.id SEPARATOR ', ') AS titles
                     FROM orders o JOIN order_items oi ON oi.order_id = o.id WHERE o.user_id = ? GROUP BY o.id ORDER BY o.id DESC");
$od->execute([$u['id']]);
$orders = $od->fetchAll();

page_start('Your library');
echo h1('Your library');
?>
<p class="mt-3 font-mono text-paper/70"><?= e($u['name']) ?>, <?= e($u['email']) ?></p>

<?php if (!$library): ?>
  <div class="mt-10 border-4 border-dashed border-paper/30 p-12 text-center">
    <p class="font-display font-black uppercase text-4xl">No films yet</p>
    <p class="mt-2 text-paper/80">Films you buy are kept here.</p>
    <a href="index.html#wall" class="inline-block mt-6 bg-blood font-stencil uppercase px-6 py-3 slab press">Browse the wall</a>
  </div>
<?php else: ?>
  <div class="mt-10 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">
    <?php foreach ($library as $i => $m): ?>
      <a href="movie.php?id=<?= (int)$m['id'] ?>" class="block tilt" style="--r:<?= [-1.4, 1.1, -0.7, 1.5, -1][$i % 5] ?>deg">
        <img src="<?= e(poster_src($m)) ?>" alt="Poster for <?= e($m['title']) ?>" loading="lazy" width="600" height="900" class="w-full aspect-[2/3] object-cover border-2 border-paper/25">
        <span class="block mt-2 font-display font-extrabold uppercase text-lg leading-none"><?= e($m['title']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php if ($orders): ?>
<section class="mt-20">
  <h2 class="font-display font-black uppercase text-4xl leading-none mb-5">Orders</h2>
  <div class="overflow-x-auto">
  <table class="w-full font-mono text-sm text-left border-2 border-paper/30">
    <thead class="bg-concrete"><tr><th class="p-3">Order</th><th class="p-3">Date</th><th class="p-3">Films</th><th class="p-3">Paid with</th><th class="p-3 text-right">Total</th></tr></thead>
    <tbody class="divide-y divide-paper/20">
    <?php foreach ($orders as $o): ?>
      <tr><td class="p-3 font-bold">#<?= (int)$o['id'] ?></td><td class="p-3 whitespace-nowrap"><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td><td class="p-3"><?= e($o['titles']) ?></td><td class="p-3"><?= e($o['payment_method']) ?></td><td class="p-3 text-right font-bold"><?= money($o['total']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</section>
<?php endif; ?>
<?php page_end();
