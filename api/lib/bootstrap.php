<?php
/*
 * Společný základ API poukazů: konfigurace, úložiště, číslování, pomocné funkce.
 * Nic se tu nevypisuje — výstup řeší jednotlivé vstupní skripty.
 */
declare(strict_types=1);

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Prague');
ini_set('display_errors', '0');

const ROOT = __DIR__ . '/../..';          // kořen webu (/www)
const LIB  = __DIR__;

/* ═══ Konfigurace ═══
   Hledá se nejdřív mimo web (/poukazy-data/config.php vedle /www), pak api/config.php. */
function cfg(?string $klic = null)
{
    static $c = null;
    if ($c === null) {
        $kandidati = [ROOT . '/../poukazy-data/config.php', __DIR__ . '/../config.php'];
        foreach ($kandidati as $f) {
            if (is_file($f)) { $c = require $f; break; }
        }
        if ($c === null) $c = require __DIR__ . '/../config.example.php';
    }
    return $klic === null ? $c : ($c[$klic] ?? null);
}

/* ═══ Datová složka ═══
   Mimo web, pokud existuje /poukazy-data, jinak api/data (chráněná .htaccess). */
function data_dir(string $pod = ''): string
{
    $base = is_dir(ROOT . '/../poukazy-data') ? ROOT . '/../poukazy-data' : __DIR__ . '/../data';
    $d = $base . ($pod !== '' ? '/' . $pod : '');
    if (!is_dir($d)) mkdir($d, 0750, true);
    // pojistka: data uvnitř webu nesmí být nikdy dostupná přes URL
    if (!is_file($base . '/.htaccess')) file_put_contents($base . '/.htaccess', "Require all denied\n");
    return $d;
}

function loguj(string $zprava): void
{
    file_put_contents(data_dir() . '/chyby.log', date('c') . ' ' . $zprava . "\n", FILE_APPEND | LOCK_EX);
}

/* ═══ Ceník — jediný zdroj pravdy ═══ */
function cenik(): array
{
    static $c = null;
    if ($c === null) {
        $c = json_decode((string) file_get_contents(ROOT . '/assets/data/poukazy-cenik.json'), true, 16, JSON_THROW_ON_ERROR);
    }
    return $c;
}

function masaz(string $id): ?array
{
    foreach (cenik()['masaze'] as $m) if ($m['id'] === $id) return $m;
    return null;
}

/* ═══ Číslování — atomicky přes flock ═══
   Řada se každý rok začíná znovu od 1: objednávky UT-2026-0001, doklady 2026-0001. */
function dalsi_cislo(string $rada): int
{
    $rok = date('Y');
    $f = data_dir('citace') . "/$rada-$rok.txt";
    $h = fopen($f, 'c+');
    if (!$h || !flock($h, LOCK_EX)) throw new RuntimeException('Nelze zamknout počítadlo');
    $n = (int) stream_get_contents($h) + 1;
    ftruncate($h, 0); rewind($h); fwrite($h, (string) $n); fflush($h);
    flock($h, LOCK_UN); fclose($h);
    return $n;
}

/* ═══ Objednávky — jeden JSON soubor na objednávku ═══ */
function obj_soubor(string $id): string
{
    if (!preg_match('/^UT-\d{4}-\d{4,}$/', $id)) throw new InvalidArgumentException('Neplatné číslo objednávky');
    return data_dir('objednavky') . "/$id.json";
}

function obj_nacti(string $id): ?array
{
    $f = obj_soubor($id);
    return is_file($f) ? json_decode((string) file_get_contents($f), true) : null;
}

function obj_uloz(array $o): void
{
    file_put_contents(obj_soubor($o['id']), json_encode($o, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX);
}

function obj_vsechny(): array
{
    $out = [];
    foreach (glob(data_dir('objednavky') . '/UT-*.json') ?: [] as $f) {
        $out[] = json_decode((string) file_get_contents($f), true);
    }
    return $out;
}

/* přehled pro Lukáše — jeden řádek na objednávku, otevře se v Excelu */
function obj_do_csv(array $o): void
{
    $f = data_dir() . '/objednavky.csv';
    $nova = !is_file($f);
    $h = fopen($f, 'a');
    flock($h, LOCK_EX);
    if ($nova) {
        fwrite($h, "\xEF\xBB\xBF");
        fputcsv($h, ['Datum', 'Objednávka', 'VS', 'Poukaz', 'Celkem Kč', 'Doručení', 'Termín', 'Kupující', 'E-mail', 'Telefon'], ';', '"', '');
    }
    fputcsv($h, [date('j. n. Y H:i'), $o['id'], $o['vs'], nazev_poukazu($o), $o['celkem'], $o['doruceni'],
        $o['termin'] ? datum_cz($o['termin']) : 'co nejdřív', $o['kupujici']['jmeno'], $o['kupujici']['email'], $o['kupujici']['telefon']], ';', '"', '');
    flock($h, LOCK_UN); fclose($h);
}

/* ═══ Formátování ═══ */
function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* 1290 → „1 290 Kč“ s nezlomitelnými mezerami */
function kc(int $n, bool $html = true): string
{
    $s = number_format($n, 0, ',', ' ') . ' Kč';
    return $html ? str_replace(' ', '&nbsp;', $s) : $s;
}

const DNY = ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'];

/* '2026-12-24' → „24. 12. 2026“, s $den → „24. 12. 2026 (čtvrtek)“ */
function datum_cz(string $ymd, bool $den = false): string
{
    $t = strtotime($ymd . ' 12:00');
    $s = date('j. n. Y', $t);
    return $den ? $s . ' (' . DNY[(int) date('w', $t)] . ')' : $s;
}

function nazev_poukazu(array $o): string
{
    return $o['typ'] === 'hodnota'
        ? 'Hodnotový poukaz ' . kc($o['hodnota'], false)
        : $o['masaz_nazev'] . ', ' . $o['delka'] . ' minut';
}

/* Odečte pracovní dny (víkendy přeskakuje; státní svátky ne — orientační údaj pro Lukáše). */
function pracovni_dny_zpet(string $ymd, int $n): string
{
    $t = strtotime($ymd . ' 12:00');
    while ($n > 0) {
        $t -= 86400;
        if ((int) date('N', $t) < 6) $n--;
    }
    return date('Y-m-d', $t);
}

/* ═══ Platba ═══ */
function iban_cz(string $ucet): string
{
    if (!preg_match('/^(?:(\d{1,6})-)?(\d{2,10})\/(\d{4})$/', str_replace(' ', '', $ucet), $m)) {
        throw new InvalidArgumentException('Neplatné číslo účtu');
    }
    $bban = $m[3] . str_pad($m[1], 6, '0', STR_PAD_LEFT) . str_pad($m[2], 10, '0', STR_PAD_LEFT);
    // CZ = 12 35, kontrolní číslice podle ISO 13616 (mod 97 po kouscích, bez bcmath)
    $num = $bban . '123500';
    $mod = 0;
    foreach (str_split($num, 7) as $kus) $mod = (int) (($mod . $kus) % 97);
    return 'CZ' . str_pad((string) (98 - $mod), 2, '0', STR_PAD_LEFT) . $bban;
}

/* QR Platba (formát SPAYD) — načte ji každá česká bankovní aplikace */
function spayd(array $o): string
{
    return 'SPD*1.0*ACC:' . iban_cz(cfg('ucet')) . '*AM:' . number_format($o['celkem'], 2, '.', '')
        . '*CC:CZK*X-VS:' . $o['vs'] . '*MSG:POUKAZ ' . $o['id'];
}

/* ═══ HTTP ═══ */
function chce_json(): bool
{
    return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}

function json_out(int $kod, array $data): never
{
    http_response_code($kod);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* IP se nikam neukládá v čitelné podobě — jen otisk kvůli limitu odeslání */
function ip_otisk(): string
{
    return substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . date('Y-m')), 0, 16);
}
