<?php
/*
 * JEN PRO VÝVOJ: náhled e-mailů uložených v režimu mail_mode = 'log'.
 * Funguje jen s 'dev' => true v config.php. Na ostrém serveru je vypnutý.
 */
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';

if (!cfg('dev') || cfg('mail_mode') !== 'log') { http_response_code(404); exit; }

$dir = data_dir('posta');
if (isset($_GET['f'])) {
    [$z, $f] = array_pad(explode('/', (string) $_GET['f'], 2), 2, '');
    $cesta = "$dir/" . basename($z) . '/' . basename($f);
    if (!is_file($cesta)) { http_response_code(404); exit; }
    $typ = ['html' => 'text/html; charset=utf-8', 'pdf' => 'application/pdf', 'png' => 'image/png', 'txt' => 'text/plain; charset=utf-8', 'json' => 'application/json'];
    header('Content-Type: ' . ($typ[pathinfo($cesta, PATHINFO_EXTENSION)] ?? 'application/octet-stream'));
    // relativní obrázky ve zprávě musí mířit zpátky sem
    if (str_ends_with($cesta, '.html')) echo preg_replace('/src="([a-z0-9_-]+\.png)"/i', 'src="dev-posta.php?f=' . rawurlencode(basename($z)) . '/$1"', (string) file_get_contents($cesta));
    else readfile($cesta);
    exit;
}

$zpravy = array_reverse(glob("$dir/*", GLOB_ONLYDIR) ?: []);
echo '<!DOCTYPE html><meta charset="utf-8"><title>Odeslaná pošta (vývoj)</title><link rel="stylesheet" href="../assets/css/podstranka.css">'
    . '<style>body{padding:2rem}table{border-collapse:collapse;width:100%;font-size:.9rem}td,th{text-align:left;padding:.6rem;border-bottom:1px solid var(--c-sand-lt);vertical-align:top}a{color:var(--c-forest);text-decoration:underline}</style>'
    . '<h1 style="color:var(--c-forest);margin-bottom:1rem">Pošta — vývojový režim</h1><p>E-maily se neodesílají, ukládají se sem. Nejnovější nahoře.</p>'
    . '<table><tr><th>Čas</th><th>Komu</th><th>Předmět</th><th>Přílohy</th></tr>';
foreach ($zpravy as $z) {
    $h = json_decode((string) file_get_contents("$z/hlavicka.json"), true);
    $b = rawurlencode(basename($z));
    $pr = implode('<br>', array_map(fn($p) => '<a href="?f=' . $b . '/' . rawurlencode($p) . '" target="_blank">' . e($p) . '</a>', $h['prilohy']));
    echo '<tr><td>' . e(date('j. n. H:i:s', strtotime($h['cas']))) . '</td><td>' . e($h['komu']) . '</td><td><a href="?f=' . $b . '/zprava.html" target="_blank">' . e($h['predmet']) . '</a></td><td>' . ($pr ?: '—') . '</td></tr>';
}
echo '</table>';
