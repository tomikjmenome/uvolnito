<?php
/*
 * Denní odeslání naplánovaných e-mailových poukazů.
 * WEDOS → Plánované úlohy (CRON): jednou denně ráno zavolat
 *   https://uvolnito.cz/api/cron.php?klic=<cron_klic z config.php>
 * nebo z příkazové řádky: php api/cron.php
 */
declare(strict_types=1);

require __DIR__ . '/lib/bootstrap.php';
require_once LIB . '/akce.php';

if (PHP_SAPI !== 'cli') {
    $klic = (string) cfg('cron_klic');
    if ($klic === '' || !hash_equals($klic, (string) ($_GET['klic'] ?? ''))) {
        http_response_code(403);
        exit('Zakázáno.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

$hotovo = odesli_naplanovane();
echo date('c') . ' — odesláno: ' . ($hotovo ? implode(', ', $hotovo) : 'nic') . "\n";
