<?php
/*
 * Co se děje po připsání platby. Volá to api/potvrdit.php (Lukáš klikne v e-mailu)
 * a api/cron.php (odeslání naplánovaných e-mailových poukazů v den doručení).
 *
 * Stavy objednávky:
 *   ceka_na_platbu → (Lukáš potvrdí platbu) →
 *     e-mail, hned / datum už je    → odeslano
 *     e-mail, datum v budoucnu      → naplanovano → (cron v den D) → odeslano
 *     pošta                         → k_odeslani_postou (Lukáš dostal PDF k tisku)
 */
declare(strict_types=1);

require_once LIB . '/dokumenty.php';
require_once LIB . '/posta.php';
require_once LIB . '/sablony.php';

const STAVY = [
    'ceka_na_platbu'    => 'Čeká na platbu',
    'naplanovano'       => 'Zaplaceno, poukaz odejde v den doručení',
    'odeslano'          => 'Poukaz odeslán zákazníkovi',
    'k_odeslani_postou' => 'Zaplaceno, poukaz je potřeba vytisknout a odeslat poštou',
];

function vystav(array $o): array
{
    $dnes = date('Y-m-d');
    $o['zaplaceno_dne'] = $dnes;
    $o['doklad_cislo'] = date('Y') . '-' . str_pad((string) dalsi_cislo('doklad'), 4, '0', STR_PAD_LEFT);
    $o['platnost_do'] = date('Y-m-d', strtotime('+' . cenik()['platnost_mesicu'] . ' months', strtotime($dnes)));
    return $o;
}

function nazev_pdf(array $o, string $co): string
{
    return $co === 'poukaz' ? "darkovy-poukaz-{$o['id']}.pdf" : "doklad-{$o['doklad_cislo']}.pdf";
}

function posli_poukaz(array $o): array
{
    $m = email_poukaz_zakaznik($o);
    $prilohy = [nazev_pdf($o, 'poukaz') => poukaz_pdf($o)];
    if (empty($o['doklad_odeslan'])) $prilohy[nazev_pdf($o, 'doklad')] = doklad_pdf($o);
    $ok = posli($m + ['komu' => $o['kupujici']['email'], 'jmeno' => $o['kupujici']['jmeno'], 'reply_to' => cfg('odpovedi_na'), 'prilohy' => $prilohy]);
    if (!$ok) throw new RuntimeException('Poukaz se nepodařilo odeslat e-mailem.');
    $o['doklad_odeslan'] = true;
    $o['stav'] = 'odeslano';
    $o['odeslano_dne'] = date('Y-m-d H:i');
    return $o;
}

/* Lukáš potvrdil platbu */
function platba_prisla(array $o): array
{
    if ($o['stav'] !== 'ceka_na_platbu') return $o;
    $o = vystav($o);
    $dnes = date('Y-m-d');

    if ($o['doruceni'] === 'email' && (!$o['termin'] || $o['termin'] <= $dnes)) {
        $o = posli_poukaz($o);
    } else {
        // zákazník dostane doklad a informaci, kdy poukaz přijde
        $m = email_zaplaceno_zakaznik($o);
        $ok = posli($m + ['komu' => $o['kupujici']['email'], 'jmeno' => $o['kupujici']['jmeno'], 'reply_to' => cfg('odpovedi_na'),
            'prilohy' => [nazev_pdf($o, 'doklad') => doklad_pdf($o)]]);
        if (!$ok) throw new RuntimeException('Doklad se nepodařilo odeslat e-mailem.');
        $o['doklad_odeslan'] = true;

        if ($o['doruceni'] === 'posta') {
            $m = email_posta_lukas($o);
            posli($m + ['komu' => cfg('kopie_objednavek'), 'prilohy' => [nazev_pdf($o, 'poukaz') => poukaz_pdf($o)]]);
            $o['stav'] = 'k_odeslani_postou';
        } else {
            $o['stav'] = 'naplanovano';
        }
    }
    obj_uloz($o);
    return $o;
}

/* Denní běh: pošle e-mailové poukazy, kterým dnes nastal den doručení */
function odesli_naplanovane(): array
{
    $hotovo = [];
    foreach (obj_vsechny() as $o) {
        if (($o['stav'] ?? '') !== 'naplanovano' || $o['termin'] > date('Y-m-d')) continue;
        try {
            $o = posli_poukaz($o);
            obj_uloz($o);
            $hotovo[] = $o['id'];
        } catch (Throwable $e) {
            loguj("Cron: {$o['id']} — " . $e->getMessage());
        }
    }
    return $hotovo;
}
