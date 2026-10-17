<?php
require __DIR__ . '/includes/layout.php';
$u = require_login();
$pdo = db();

// anything already owned shouldn't be buyable twice
$owned = owned_ids($u['id']);
if ($owned) {
    $in = implode(',', array_fill(0, count($owned), '?'));
    $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND movie_id IN ($in)")->execute([$u['id'], ...$owned]);
}

function load_cart(PDO $pdo, int $uid): array {
    $st = $pdo->prepare('SELECT m.id, m.title, m.price FROM cart_items c JOIN movies m ON m.id = c.movie_id WHERE c.user_id = ? ORDER BY c.id');
    $st->execute([$uid]);
    return $st->fetchAll();
}

$methods = ['Card (demo)', 'E-wallet (demo)', 'Bank transfer (demo)'];
$items = load_cart($pdo, $u['id']);
if (!$items && $_SERVER['REQUEST_METHOD'] !== 'POST') { flash('Your cart is empty.', 'err'); redirect('cart.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $method = (string)($_POST['method'] ?? '');
    if (!in_array($method, $methods, true)) {
        $error = 'Pick a payment method.';
    } elseif (!$items) {
        $error = 'Your cart is empty.';
    } else {
        try {
            $pdo->beginTransaction();
            $items = load_cart($pdo, $u['id']); // re-read inside the transaction, prices come from the DB never the browser
            $cents = array_sum(array_map(fn($i) => (int)round($i['price'] * 100), $items));
            $pdo->prepare("INSERT INTO orders (user_id, total, payment_method, status) VALUES (?, ?, ?, 'paid')")
                ->execute([$u['id'], number_format($cents / 100, 2, '.', ''), $method]);
            $orderId = (int)$pdo->lastInsertId();
            $ins = $pdo->prepare('INSERT INTO order_items (order_id, movie_id, title_snapshot, price) VALUES (?, ?, ?, ?)');
            foreach ($items as $i) $ins->execute([$orderId, $i['id'], $i['title'], $i['price']]);
            $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([$u['id']]);
            $pdo->commit();
            flash('Order #' . $orderId . ' is paid. Your films are in the library.');
            redirect('account.php');
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = 'The order did not go through. Nothing was charged, try again.';
        }
    }
}
$total = array_sum(array_map(fn($i) => (float)$i['price'], $items));

page_start('Check out', 'max-w-4xl');
echo h1('Check out');
?>
<div class="mt-10 grid md:grid-cols-2 gap-10">
  <section>
    <h2 class="font-stencil uppercase text-xl mb-3">Your films</h2>
    <ul class="divide-y-2 divide-paper/20 border-y-2 border-paper/20 font-mono">
      <?php foreach ($items as $i): ?>
        <li class="flex justify-between gap-4 py-3"><span><?= e($i['title']) ?></span><span class="font-bold"><?= money($i['price']) ?></span></li>
      <?php endforeach; ?>
    </ul>
    <p class="mt-4 flex justify-between font-display font-black text-4xl"><span>Total</span><span><?= money($total) ?></span></p>
  </section>

  <form method="post" class="bg-charcoal border-2 border-paper/40 p-6 slab space-y-5">
    <?= csrf_field() ?>
    <?php if ($error): ?><p class="bg-blood text-paper font-mono font-bold p-3" role="alert"><?= e($error) ?></p><?php endif; ?>
    <fieldset>
      <legend class="font-stencil uppercase text-xl mb-3">Pay with</legend>
      <?php foreach ($methods as $k => $mt): ?>
        <label class="flex items-center gap-3 border-2 border-paper/40 p-3 mb-2 cursor-pointer has-[:checked]:border-hazard has-[:checked]:bg-asphalt">
          <input type="radio" name="method" value="<?= e($mt) ?>" required <?= $k === 0 ? 'checked' : '' ?> class="accent-[#E32636] w-4 h-4">
          <span class="font-mono"><?= e($mt) ?></span>
        </label>
      <?php endforeach; ?>
    </fieldset>
    <p class="font-mono text-sm text-paper/70">Demo mode: no card details are collected and no money moves.</p>
    <button class="bg-blood font-stencil uppercase text-lg px-6 py-3 slab press">Pay <?= money($total) ?></button>
  </form>
</div>
<?php page_end();
