<?php
// Generates an original gig-poster style SVG for each movie, so the site works with zero image files.
// Admins can still paste a real poster URL on any movie and that wins.
require __DIR__ . '/../includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$st = db()->prepare('SELECT m.title, m.release_year, m.rating, m.director, g.name AS genre
                     FROM movies m LEFT JOIN genres g ON g.id = m.genre_id WHERE m.id = ?');
$st->execute([$id]);
$m = $st->fetch() ?: ['title' => 'No poster', 'release_year' => '', 'rating' => '', 'director' => '', 'genre' => ''];

// [background, ink, accent]
$palettes = [
    ['#E32636', '#101010', '#F5D547'],
    ['#E8E0D0', '#101010', '#E32636'],
    ['#F5D547', '#101010', '#E32636'],
    ['#1B1B1B', '#E8E0D0', '#E32636'],
    ['#292929', '#F5D547', '#E8E0D0'],
    ['#E8E0D0', '#E32636', '#101010'],
];
[$bg, $ink, $acc] = $palettes[$id % count($palettes)];
$variant = $id % 3;

// wrap title into short stacked lines
$words = preg_split('/\s+/', strtoupper($m['title']));
$lines = []; $cur = '';
foreach ($words as $w) {
    if ($cur !== '' && strlen($cur . ' ' . $w) > 11) { $lines[] = $cur; $cur = $w; }
    else { $cur = $cur === '' ? $w : $cur . ' ' . $w; }
}
if ($cur !== '') $lines[] = $cur;
$maxChars = max(array_map('strlen', $lines));
$size = (int)min(170, 520 / max(1, $maxChars * 0.55), 400 / (count($lines) * 0.95));
$size = max(54, $size);

$x = fn($s) => htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
$impact = "Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif";
$mono   = "'Courier New', monospace";

$svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 900" role="img" aria-label="' . $x($m['title']) . '">';
$svg .= '<defs><pattern id="d" width="14" height="14" patternUnits="userSpaceOnUse" patternTransform="rotate(30)"><circle cx="7" cy="7" r="3" fill="' . $ink . '" opacity=".16"/></pattern></defs>';
$svg .= '<rect width="600" height="900" fill="' . $bg . '"/><rect width="600" height="900" fill="url(#d)"/>';
$svg .= '<polygon points="0,640 600,510 600,640 0,770" fill="' . $acc . '"/>';
if ($variant === 0) {
    $svg .= '<circle cx="470" cy="600" r="150" fill="none" stroke="' . $ink . '" stroke-width="12" opacity=".35"/>';
} elseif ($variant === 1) {
    for ($i = 0; $i < 4; $i++) $svg .= '<rect x="' . (330 + $i * 52) . '" y="440" width="26" height="300" fill="' . $ink . '" opacity=".28" transform="rotate(18 450 590)"/>';
} else {
    $svg .= '<g stroke="' . $ink . '" stroke-width="16" opacity=".3"><line x1="360" y1="470" x2="570" y2="700"/><line x1="570" y1="470" x2="360" y2="700"/></g>';
}
$svg .= '<rect x="26" y="26" width="548" height="848" fill="none" stroke="' . $ink . '" stroke-width="6"/>';
$svg .= '<text x="48" y="74" font-family="' . $mono . '" font-weight="bold" font-size="22" fill="' . $ink . '">' . $x(strtoupper($m['genre'] ?? '')) . '</text>';
$y = 110 + $size * 0.85;
foreach ($lines as $ln) {
    $svg .= '<text x="46" y="' . round($y) . '" font-family="' . $impact . '" font-size="' . $size . '" letter-spacing="-1" fill="' . $ink . '">' . $x($ln) . '</text>';
    $y += $size * 0.95;
}
$svg .= '<text x="48" y="812" font-family="' . $mono . '" font-size="18" fill="' . $ink . '">A FILM BY</text>';
$svg .= '<text x="48" y="846" font-family="' . $impact . '" font-size="30" fill="' . $ink . '">' . $x(strtoupper($m['director'])) . '</text>';
$svg .= '<rect x="474" y="796" width="78" height="52" fill="none" stroke="' . $ink . '" stroke-width="4"/>';
$svg .= '<text x="513" y="831" text-anchor="middle" font-family="' . $impact . '" font-size="28" fill="' . $ink . '">' . $x($m['rating']) . '</text>';
$svg .= '<text x="552" y="74" text-anchor="end" font-family="' . $mono . '" font-weight="bold" font-size="22" fill="' . $ink . '">' . $x($m['release_year']) . '</text>';
$svg .= '</svg>';

header('Content-Type: image/svg+xml; charset=utf-8');
header('Cache-Control: public, max-age=300');
echo $svg;
