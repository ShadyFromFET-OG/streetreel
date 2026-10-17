<?php
/* Shared page chrome for every PHP page. Usage:  page_start('Title'); ...content...; page_end(); */
require_once __DIR__ . '/bootstrap.php';

function brand_html(): string {
    $parts = explode(' ', BRAND_NAME, 2);
    if (count($parts) < 2) return '<span>' . e(BRAND_NAME) . '</span>';
    return '<span>' . e($parts[0]) . '</span><span class="bg-blood text-paper px-2 -rotate-3 inline-block ml-1">' . e($parts[1]) . '</span>';
}

function page_start(string $title, string $main = 'max-w-7xl'): void {
    $u = current_user();
    $count = cart_count($u['id'] ?? null);
    $flash = take_flash();
    ?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?= e(csrf_token()) ?>">
<title><?= e($title) ?> | <?= e(BRAND_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600&family=Big+Shoulders+Display:wght@800;900&family=Courier+Prime:wght@400;700&family=Permanent+Marker&family=Saira+Stencil+One&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: { extend: {
    colors: { asphalt:'#101010', charcoal:'#1B1B1B', concrete:'#292929', blood:'#E32636', paper:'#E8E0D0', hazard:'#F5D547' },
    fontFamily: {
      display:['"Big Shoulders Display"','Impact','sans-serif'],
      stencil:['"Saira Stencil One"','Impact','sans-serif'],
      marker:['"Permanent Marker"','cursive'],
      mono:['"Courier Prime"','"Courier New"','monospace'],
      body:['Barlow','system-ui','sans-serif']
    }
  } }
};
</script>

<style>
:root{--asphalt:#101010;--charcoal:#1B1B1B;--concrete:#292929;--blood:#E32636;--paper:#E8E0D0;--hazard:#F5D547}
html{background:var(--asphalt)}
body{background:var(--asphalt);color:var(--paper);font-family:'Barlow',system-ui,sans-serif;min-height:100vh}
/* photocopier grain over everything */
body::before{content:"";position:fixed;inset:0;z-index:55;pointer-events:none;opacity:.08;mix-blend-mode:screen;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='220' height='220'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E")}
/* industrial warning tape */
.hazard{background:repeating-linear-gradient(-45deg,var(--hazard) 0 16px,var(--asphalt) 16px 32px)}
/* masking tape holding posters up */
.tape{position:absolute;z-index:2;width:84px;height:26px;background:rgba(245,213,71,.8);box-shadow:0 1px 3px rgba(0,0,0,.5);transform:rotate(var(--tr,-6deg))}
.tilt{transform:rotate(var(--r,0deg))}
/* hard offset shadow, like a pasted sticker */
.slab{box-shadow:5px 5px 0 #000}
.slab-sm{box-shadow:3px 3px 0 #000}
.press{transition:transform .08s,box-shadow .08s}
.press:active{transform:translate(3px,3px);box-shadow:0 0 0 #000}
/* ripped paper edge */
.torn-b{clip-path:polygon(0 0,100% 0,100% 94%,96% 97%,92% 93%,87% 98%,82% 94%,76% 99%,71% 95%,65% 98%,59% 94%,53% 99%,47% 95%,41% 98%,35% 93%,29% 97%,23% 94%,17% 99%,11% 95%,5% 98%,0 94%)}
.paper-sheet{background:var(--paper);color:var(--asphalt)}
/* the one orchestrated moment: posters slapped onto the wall */
@keyframes slap{0%{opacity:0;transform:translateY(-46px) scale(1.18) rotate(calc(var(--r)*2.5))}60%{opacity:1;transform:translateY(0) scale(.97) rotate(var(--r))}100%{opacity:1;transform:rotate(var(--r))}}
.slap{animation:slap .5s cubic-bezier(.2,.9,.3,1.15) both;animation-delay:calc(var(--i)*.15s)}
:focus-visible{outline:3px solid var(--hazard);outline-offset:3px}
input,select,textarea{border-radius:0}
::selection{background:var(--blood);color:var(--paper)}
@media (prefers-reduced-motion:reduce){*,*::before,*::after{animation:none!important;transition:none!important}}

</style>
</head>
<body class="font-body antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-hazard focus:text-asphalt focus:p-2">Skip to content</a>
<header class="sticky top-0 z-40 bg-asphalt border-b-4 border-blood">
  <div class="mx-auto max-w-7xl px-4 py-3 flex items-center gap-x-5 gap-y-1 flex-wrap">
    <a href="index.html" class="font-display font-black uppercase text-3xl leading-none tracking-tight flex items-center"><?= brand_html() ?></a>
    <nav class="ml-auto flex items-center gap-x-5 gap-y-1 flex-wrap font-stencil text-sm uppercase" aria-label="Main">
      <a href="index.html#wall" class="hover:text-hazard">The wall</a>
      <a href="cart.php" class="hover:text-hazard">Cart <span class="inline-block min-w-[1.5rem] text-center bg-hazard text-asphalt font-mono font-bold px-1"><?= $count ?></span></a>
      <?php if ($u): ?>
        <a href="account.php" class="hover:text-hazard"><?= e($u['name']) ?></a>
        <?php if ($u['role'] === 'admin'): ?><a href="admin.php" class="text-hazard hover:underline">Admin</a><?php endif; ?>
        <form action="logout.php" method="post" class="inline"><?= csrf_field() ?><button class="hover:text-blood uppercase">Log out</button></form>
      <?php else: ?>
        <a href="login.php" class="hover:text-hazard">Log in</a>
        <a href="register.php" class="bg-blood px-3 py-1 slab-sm press">Sign up</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<?php if ($flash): ?>
  <div role="status" class="<?= $flash[1] === 'err' ? 'bg-blood text-paper' : 'bg-hazard text-asphalt' ?> font-mono font-bold text-center py-2 px-4"><?= e($flash[0]) ?></div>
<?php endif; ?>
<main id="main" class="mx-auto <?= e($main) ?> px-4 py-10">
<?php
}

function page_end(): void { ?>
</main>
<footer class="mt-16">
  <div class="hazard h-4"></div>
  <div class="mx-auto max-w-7xl px-4 py-8 flex flex-wrap items-center justify-between gap-4 font-mono text-sm text-paper/70">
    <span class="font-display font-black uppercase text-2xl text-paper"><?= e(BRAND_NAME) ?></span>
    <span>Demo store. Every film and name here is made up and no real payments are taken.</span>
  </div>
</footer>
<script>
async function reelCart(action, movieId) {
  const token = document.querySelector('meta[name="csrf-token"]').content;
  const r = await fetch('api/cart.php', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': token }, body: JSON.stringify({ action, movie_id: movieId }) });
  const data = await r.json().catch(() => ({}));
  if (!r.ok) { const err = new Error(data.error || 'Something went wrong.'); err.status = r.status; throw err; }
  return data;
}
</script>
</body>
</html>
<?php
}

/** Reusable heading style for page titles */
function h1(string $text, string $extra = ''): string {
    return '<h1 class="font-display font-black uppercase leading-[.85] tracking-tight text-5xl sm:text-7xl ' . $extra . '">' . e($text) . '</h1>';
}
