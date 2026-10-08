<?php
/*
 * Příjem objednávky dárkového poukazu z poukazy.html.
 * S JavaScriptem odpovídá JSONem, bez něj vykreslí jednoduchou stránku.
 * Cena se počítá VŽDY tady z assets/data/poukazy-cenik.json — formuláři se nevěří.
 */
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require_once LIB . '/akce.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Jen POST.');
}

$in = $_POST;
$chyby = [];
$t = fn(string $k, int $max) => trim(preg_replace('/[\x00-\x09\x0B-\x1F\x7F]/u', '', mb_substr((string) ($in[$k] ?? ''), 0, $max + 50)));

/* ═══ Antispam ═══
   Vyplněná past nebo odeslání do 4 s od načtení stránky = robot. Dostane „úspěch“,
   ať nezkouší dál, ale nic se neuloží ani neodešle. */
$ts = (int) ($in['ts'] ?? 0);
if (($in['web'] ?? '') !== '' || ($ts > 0 && time() - intdiv($ts, 1000) < 4)) {
    chce_json() ? json_out(200, ['ok' => true, 'cislo' => 'UT-0000-0000']) : exit('Díky.');
}
$rl = data_dir('limit') . '/' . ip_otisk();
$casy = array_filter(array_map('intval', is_file($rl) ? file($rl, FILE_IGNORE_NEW_LINES) : []), fn($c) => $c > time() - 3600);
if (count($casy) >= 5) {
    $chyby['_'] = 'Z tvého připojení přišlo několik objednávek za sebou. Zkus to prosím za hodinu, nebo zavolej na +420 608 654 708.';
}

/* ═══ Poukaz ═══ */
$c = cenik();
$o = ['typ' => $in['typ'] ?? '', 'masaz_id' => null, 'masaz_nazev' => null, 'delka' => null, 'hodnota' => null];
if ($o['typ'] === 'masaz') {
    $m = masaz((string) ($in['masaz'] ?? ''));
    $delka = (string) ($in['delka'] ?? '');
    if (!$m) $chyby['masaz'] = 'Vyber masáž.';
    elseif (!isset($m['delky'][$delka])) {
        if (count($m['delky']) === 1) $delka = (string) array_key_first($m['delky']);
        else $chyby['delka'] = 'Vyber délku masáže.';
    }
    if ($m && isset($m['delky'][$delka])) {
        $o['masaz_id'] = $m['id']; $o['masaz_nazev'] = $m['nazev']; $o['delka'] = (int) $delka;
        $o['cena_poukazu'] = (int) $m['delky'][$delka];
    }
} elseif ($o['typ'] === 'hodnota') {
    $h = (int) ($in['hodnota'] ?? 0);
    if (!in_array($h, $c['hodnoty'], true)) $chyby['hodnota'] = 'Vyber hodnotu poukazu.';
    else { $o['hodnota'] = $h; $o['cena_poukazu'] = $h; }
} else {
    $chyby['typ'] = 'Vyber, jestli chceš poukaz na masáž, nebo na částku.';
}

/* ═══ Věnování ═══ */
foreach (['pro' => 40, 'od' => 40, 'vzkaz' => 160] as $k => $max) {
    $o[$k] = $k === 'vzkaz' ? trim((string) preg_replace('/\R{2,}/u', "\n", str_replace("\r", '', $t($k, $max)))) : preg_replace('/\s+/u', ' ', $t($k, $max));
    if (mb_strlen($o[$k]) > $max) $chyby[$k] = "Maximálně $max znaků.";
}
if (substr_count($o['vzkaz'], "\n") > 2) $chyby['vzkaz'] = 'Vzkaz může mít nejvýš 3 řádky.';

/* ═══ Doručení a termín ═══ */
$o['doruceni'] = in_array($in['doruceni'] ?? '', ['email', 'posta'], true) ? $in['doruceni'] : null;
if (!$o['doruceni']) $chyby['doruceni'] = 'Vyber způsob doručení.';
if ($o['doruceni'] === 'posta' && empty($c['posta_aktivni'])) $chyby['doruceni'] = 'Doručení poštou zatím připravujeme. Vyber prosím e-mail.';
$o['postovne'] = $o['doruceni'] === 'posta' ? (int) $c['postovne'] : 0;

$o['termin'] = null;
if (($in['termin_typ'] ?? 'hned') === 'datum' && $o['doruceni']) {
    $d = (string) ($in['termin'] ?? '');
    $min = date('Y-m-d', strtotime($o['doruceni'] === 'email' ? '+1 day' : '+7 days'));
    $max = date('Y-m-d', strtotime('+180 days'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) || !checkdate((int) substr($d, 5, 2), (int) substr($d, 8, 2), (int) substr($d, 0, 4))) {
        $chyby['termin'] = 'Vyber datum doručení.';
    } elseif ($d < $min || $d > $max) {
        $chyby['termin'] = 'Datum musí být mezi ' . datum_cz($min) . ' a ' . datum_cz($max) . '.';
    } else {
        $o['termin'] = $d;
    }
}

$o['adresa'] = null;
if ($o['doruceni'] === 'posta') {
    $a = ['jmeno' => $t('adr_jmeno', 80), 'ulice' => $t('adr_ulice', 100), 'mesto' => $t('adr_mesto', 60), 'psc' => preg_replace('/\s+/', '', $t('adr_psc', 6))];
    if ($a['jmeno'] === '') $chyby['adr_jmeno'] = 'Komu poukaz poslat?';
    if ($a['ulice'] === '') $chyby['adr_ulice'] = 'Vyplň ulici a číslo domu.';
    if ($a['mesto'] === '') $chyby['adr_mesto'] = 'Vyplň město.';
    if (!preg_match('/^\d{5}$/', $a['psc'])) $chyby['adr_psc'] = 'PSČ má 5 číslic.';
    $a['psc'] = substr($a['psc'], 0, 3) . ' ' . substr($a['psc'], 3);
    $o['adresa'] = $a;
}

/* ═══ Kupující ═══ */
$o['kupujici'] = ['jmeno' => $t('jmeno', 80), 'email' => $t('email', 120), 'telefon' => $t('telefon', 20)];
if ($o['kupujici']['jmeno'] === '') $chyby['jmeno'] = 'Vyplň jméno a příjmení.';
if (!filter_var($o['kupujici']['email'], FILTER_VALIDATE_EMAIL)) $chyby['email'] = 'Zkontroluj e-mail, na něj přijdou platební údaje.';
if ($o['kupujici']['telefon'] !== '' && !preg_match('/^\+?[0-9 ]{9,16}$/', $o['kupujici']['telefon'])) $chyby['telefon'] = 'Telefon zadej jen číslicemi, třeba +420 777 123 456.';
if (($in['platba'] ?? 'prevod') !== 'prevod') $chyby['platba'] = 'Zatím jde platit jen převodem.';
if (empty($in['souhlas'])) $chyby['souhlas'] = 'Bez souhlasu s podmínkami nejde objednávku dokončit.';

if ($chyby) {
    if (chce_json()) json_out(422, ['ok' => false, 'chyby' => $chyby]);
    vykresli('Objednávku je potřeba opravit', '<ul>' . implode('', array_map(fn($x) => '<li>' . e($x) . '</li>', $chyby)) . '</ul>'
        . '<p><a href="javascript:history.back()">Zpátky na formulář</a></p>', 422);
}

/* ═══ Uložení ═══ */
$n = dalsi_cislo('objednavka');
$o['id'] = sprintf('UT-%s-%04d', date('Y'), $n);
$o['vs'] = (int) (date('Y') . sprintf('%04d', $n));
$o['celkem'] = $o['cena_poukazu'] + $o['postovne'];
$o['token'] = bin2hex(random_bytes(16));
$o['vytvoreno'] = date('c');
$o['stav'] = 'ceka_na_platbu';
$o['ip'] = ip_otisk();
obj_uloz($o);
obj_do_csv($o);
file_put_contents($rl, implode("\n", [...$casy, time()]), LOCK_EX);

/* ═══ E-maily ═══ */
$mz = email_objednavka_zakaznik($o);
$okZ = posli($mz + ['komu' => $o['kupujici']['email'], 'jmeno' => $o['kupujici']['jmeno'], 'reply_to' => cfg('odpovedi_na')]);
$odkaz = rtrim((string) cfg('web'), '/') . '/api/potvrdit.php?c=' . $o['id'] . '&t=' . $o['token'];
$ml = email_objednavka_lukas($o, $odkaz);
$okL = posli($ml + ['komu' => cfg('kopie_objednavek'), 'reply_to' => $o['kupujici']['email']]);

if (!$okZ || !$okL) {
    // objednávka je uložená, ale nesmí to vypadat jako úspěch — člověk by čekal e-mail, který nepřijde
    loguj("Objednávka {$o['id']} uložena, e-mail selhal (zákazník: " . ($okZ ? 'ok' : 'ne') . ', Lukáš: ' . ($okL ? 'ok' : 'ne') . ')');
    $zprava = "Objednávku {$o['id']} máme, ale nepodařilo se odeslat potvrzovací e-mail. Napiš prosím na info@uvolnitomasaze.cz nebo zavolej +420 608 654 708 — platební údaje ti pošlu ručně.";
    chce_json() ? json_out(502, ['ok' => false, 'chyby' => ['_' => $zprava]]) : vykresli('E-mail se nepodařilo odeslat', '<p>' . e($zprava) . '</p>', 502);
}

$vysledek = [
    'ok' => true, 'cislo' => $o['id'], 'vs' => $o['vs'], 'celkem' => $o['celkem'], 'celkem_text' => kc($o['celkem'], false),
    'ucet' => cfg('ucet'), 'email' => $o['kupujici']['email'], 'termin' => termin_text($o, false),
    'qr' => 'data:image/png;base64,' . base64_encode(qr_png($o)),
];
if (chce_json()) json_out(200, $vysledek);

vykresli('Díky, objednávku mám', '<p>Číslo objednávky <strong>' . $o['id'] . '</strong>. Platební údaje jsem poslal na&nbsp;' . e($o['kupujici']['email']) . '.</p>'
    . '<dl><dt>Částka</dt><dd>' . kc($o['celkem']) . '</dd><dt>Účet</dt><dd>' . e(cfg('ucet')) . '</dd><dt>Variabilní symbol</dt><dd>' . $o['vs'] . '</dd></dl>'
    . '<p><img src="' . $vysledek['qr'] . '" width="180" height="180" alt="QR Platba"></p><p><a href="../index.html">Zpátky na web</a></p>');

/* jednoduchá stránka pro návštěvníky bez JavaScriptu */
function vykresli(string $nadpis, string $obsah, int $kod = 200): never
{
    http_response_code($kod);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">'
        . '<title>' . e($nadpis) . ' | uvolni.to</title><link rel="stylesheet" href="../assets/css/podstranka.css">'
        . '<style>main{max-width:40rem;margin:0 auto;padding:4rem 1.5rem}h1{font-size:var(--fs-h2);color:var(--c-forest);margin-bottom:1.5rem}dl{display:grid;grid-template-columns:auto 1fr;gap:.4rem 1.2rem}dt{color:var(--c-ink-soft)}dd{margin:0;font-weight:500}a{color:var(--c-forest);text-decoration:underline}</style>'
        . '</head><body><main><p class="label">Dárkové poukazy</p><h1>' . e($nadpis) . '</h1>' . $obsah . '</main></body></html>';
    exit;
}
