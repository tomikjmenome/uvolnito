<?php
/*
 * Stránka pro Lukáše: otevře se z tlačítka „Platba přišla“ v e-mailu s objednávkou.
 * GET jen ZOBRAZÍ objednávku a tlačítko — samotné vystavení je až POST.
 * (Poštovní programy a antiviry odkazy v e-mailech samy otevírají kvůli kontrole;
 * kdyby akci spustil už GET, poukaz by odešel bez Lukášova kliknutí.)
 */
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require_once LIB . '/akce.php';

header('X-Robots-Tag: noindex');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');

$id = (string) ($_REQUEST['c'] ?? '');
$token = (string) ($_REQUEST['t'] ?? '');
try { $o = obj_nacti($id); } catch (InvalidArgumentException) { $o = null; }
if (!$o || !hash_equals($o['token'], $token)) stranka('Odkaz neplatí', '<p>Objednávka neexistuje nebo odkaz není kompletní. Otevři ho prosím znovu z e-mailu.</p>', 404);

// stažení PDF (pro tisk poštovního poukazu nebo kontrolu)
if (isset($_GET['pdf']) && $o['stav'] !== 'ceka_na_platbu') {
    $co = $_GET['pdf'] === 'doklad' ? 'doklad' : 'poukaz';
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . nazev_pdf($o, $co) . '"');
    echo $co === 'doklad' ? doklad_pdf($o) : poukaz_pdf($o);
    exit;
}

$zprava = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['akce'] ?? '') === 'zaplaceno') {
    try {
        $o = platba_prisla($o);
        header('Location: potvrdit.php?c=' . rawurlencode($o['id']) . '&t=' . rawurlencode($token) . '&hotovo=1', true, 303);
        exit;
    } catch (Throwable $e) {
        loguj("Potvrzení {$o['id']}: " . $e->getMessage());
        $zprava = '<p class="chyba">Nepodařilo se to dokončit: ' . e($e->getMessage()) . ' Zkus to prosím znovu, případně se ozvi správci webu.</p>';
    }
}

$odkaz = 'potvrdit.php?c=' . rawurlencode($o['id']) . '&t=' . rawurlencode($token);
$radky = [
    'Poukaz' => e(nazev_poukazu($o)),
    'Částka' => kc($o['celkem']) . ($o['postovne'] ? ' (včetně poštovného ' . kc($o['postovne']) . ')' : ''),
    'Variabilní symbol' => (string) $o['vs'],
    'Kupující' => e($o['kupujici']['jmeno']) . ', ' . e($o['kupujici']['email']),
    'Doručení' => $o['doruceni'] === 'email' ? 'e-mailem' : 'poštou',
    'Datum doručení' => termin_text($o),
    'Stav' => '<strong>' . STAVY[$o['stav']] . '</strong>',
];
$h = $zprava;
if (isset($_GET['hotovo'])) {
    $h .= '<p class="ok">' . match ($o['stav']) {
        'odeslano' => 'Hotovo. Poukaz i doklad odešly zákazníkovi na ' . e($o['kupujici']['email']) . '.',
        'naplanovano' => 'Hotovo. Zákazník dostal doklad, poukaz mu odejde sám ' . datum_cz($o['termin'], true) . '.',
        'k_odeslani_postou' => 'Hotovo. Zákazník dostal doklad. Poukaz k tisku máš v e-mailu i tady níž.',
        default => 'Hotovo.',
    } . '</p>';
}
$h .= '<dl>';
foreach ($radky as $k => $v) $h .= "<dt>$k</dt><dd>$v</dd>";
$h .= '</dl>';

if ($o['stav'] === 'ceka_na_platbu') {
    $h .= '<p>Zkontroluj v&nbsp;bance, že přišlo <strong>' . kc($o['celkem']) . '</strong> s&nbsp;VS&nbsp;<strong>' . $o['vs'] . '</strong>. Pak klikni:</p>'
        . '<form method="post" action="' . e($odkaz) . '"><input type="hidden" name="akce" value="zaplaceno">'
        . '<button class="btn" type="submit">Platba přišla — vystavit poukaz</button></form>';
} else {
    $h .= '<p><a class="btn btn-sm" href="' . e($odkaz) . '&amp;pdf=poukaz" target="_blank">Poukaz (PDF)</a> &nbsp; '
        . '<a class="link-go" href="' . e($odkaz) . '&amp;pdf=doklad" target="_blank">Doklad (PDF)</a></p>';
}
stranka('Objednávka ' . $o['id'], $h);

function stranka(string $nadpis, string $obsah, int $kod = 200): never
{
    http_response_code($kod);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">'
        . '<title>' . e($nadpis) . ' | uvolni.to</title><link rel="stylesheet" href="../assets/css/podstranka.css">'
        . '<style>body{padding:0}main{max-width:40rem;margin:0 auto;padding:3rem 1.5rem 5rem}h1{font-size:var(--fs-h2);color:var(--c-forest);margin:1rem 0 2rem}'
        . 'dl{display:grid;grid-template-columns:10rem 1fr;margin:0 0 2rem;border-top:1px solid var(--c-sand-lt)}dt,dd{padding:.7rem 0;border-bottom:1px solid var(--c-sand-lt);margin:0}dt{color:var(--c-ink-soft);font-size:.9rem}'
        . '.ok,.chyba{padding:1rem 1.2rem;border-radius:var(--radius);margin-bottom:2rem}.ok{background:var(--c-sand-lt);border-left:3px solid var(--c-forest)}.chyba{background:rgba(181,113,78,.12);border-left:3px solid var(--c-terra)}'
        . 'button.btn{cursor:pointer;font:inherit;font-size:.82rem}</style></head><body><main>'
        . '<img src="../assets/logo/ODVOZENO-wordmark-barevne.png" alt="uvolni.to" width="112" height="41"><h1>' . e($nadpis) . '</h1>' . $obsah . '</main></body></html>';
    exit;
}
