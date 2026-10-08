<?php
/*
 * Šablony e-mailů. Vzhled převzatý z email-navrh/ (schválený návrh):
 * tabulky, inline styly, šířka 600 px, obrázky jako CID přílohy.
 * Každá funkce vrací ['predmet','html','text','obrazky'].
 */
declare(strict_types=1);

const F_SERIF = "Fraunces,Georgia,'Times New Roman',serif";
const F_SANS  = "Inter,-apple-system,'Segoe UI',Arial,sans-serif";

function m_layout(string $titulek, string $preheader, string $obsah, ?string $fotoCid = null, string $fotoAlt = ''): string
{
    $foto = $fotoCid ? '<tr><td style="padding:0;line-height:0;font-size:0;"><img src="cid:' . $fotoCid . '" width="600" height="300" alt="' . e($fotoAlt)
        . '" style="display:block;border:0;width:100%;max-width:600px;height:auto;"></td></tr>' : '';
    $s = F_SANS;
    return <<<HTML
<!DOCTYPE html>
<html lang="cs"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="x-apple-disable-message-reformatting"><meta name="color-scheme" content="light"><meta name="supported-color-schemes" content="light">
<title>{$titulek}</title>
<style>body{margin:0;padding:0;}a{color:#304F47;}
@media only screen and (max-width:620px){.wrap{width:100% !important;}.px{padding-left:24px !important;padding-right:24px !important;}
.h1{font-size:30px !important;line-height:34px !important;}.stack{display:block !important;width:100% !important;}
.stack-gap{padding-top:20px !important;padding-left:0 !important;text-align:left !important;}.lbl{width:118px !important;}}</style>
</head>
<body style="margin:0;padding:0;background-color:#E4DCCC;-webkit-text-size-adjust:100%;">
<div style="display:none;max-height:0;overflow:hidden;mso-hide:all;font-size:1px;line-height:1px;color:#E4DCCC;">{$preheader}&#8199;&#847;&#8199;&#847;&#8199;&#847;&#8199;&#847;</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#E4DCCC;"><tr><td align="center" style="padding:32px 12px;">
<table role="presentation" class="wrap" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;background-color:#F7F3EC;">
<tr><td align="center" class="px" style="background-color:#304F47;padding:28px 40px 24px;">
<img src="cid:logo" width="112" height="41" alt="uvolni.to masáže" style="display:block;border:0;width:112px;height:41px;">
<p style="margin:14px 0 0;font-family:{$s};font-size:11px;line-height:16px;letter-spacing:2px;text-transform:uppercase;color:#CBBBA0;">Dárkový poukaz</p></td></tr>
{$foto}
{$obsah}
<tr><td class="px" align="center" style="background-color:#223A34;padding:28px 40px;">
<p style="margin:0 0 6px;font-family:{$s};font-size:13px;line-height:20px;color:#F7F3EC;">uvolni.to · Skřivanova 335/5, 602&nbsp;00 Brno</p>
<p style="margin:0 0 6px;font-family:{$s};font-size:13px;line-height:20px;"><a href="tel:+420608654708" style="color:#CBBBA0;text-decoration:none;">+420 608 654 708</a> &nbsp;·&nbsp; <a href="https://uvolnito.cz/" style="color:#CBBBA0;text-decoration:none;">uvolnito.cz</a></p>
<p style="margin:0;font-family:{$s};font-size:12px;line-height:18px;color:#8FA79E;">Lukáš Václavek · IČO 19696884 · neplátce DPH</p></td></tr>
</table></td></tr></table></body></html>
HTML;
}

function m_uvod(string $stitek, string $nadpis, string $odstavec): string
{
    return '<tr><td class="px" style="padding:40px 48px 8px;">'
        . '<p style="margin:0 0 12px;font-family:' . F_SANS . ';font-size:11px;line-height:16px;letter-spacing:2px;text-transform:uppercase;color:#55605C;">' . $stitek . '</p>'
        . '<h1 class="h1" style="margin:0 0 18px;font-family:' . F_SERIF . ';font-weight:400;font-size:36px;line-height:40px;letter-spacing:-0.5px;color:#304F47;">' . $nadpis . '</h1>'
        . '<p style="margin:0;font-family:' . F_SANS . ';font-size:16px;line-height:26px;color:#1B211F;">' . $odstavec . '</p></td></tr>';
}

function m_radky(array $radky): string
{
    $h = '';
    foreach ($radky as [$k, $v]) {
        $h .= '<tr><td class="lbl" width="150" style="padding:14px 0;border-bottom:1px solid #E4DCCC;font-family:' . F_SANS . ';font-size:14px;line-height:20px;color:#55605C;vertical-align:top;">' . $k . '</td>'
            . '<td style="padding:14px 0;border-bottom:1px solid #E4DCCC;font-family:' . F_SANS . ';font-size:15px;line-height:22px;color:#1B211F;">' . $v . '</td></tr>';
    }
    return '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:8px;">' . $h . '</table>';
}

function m_rekap(array $o, bool $sCenou = true, bool $sDorucenim = true): string
{
    $nazev = $o['typ'] === 'hodnota' ? 'Poukaz na&nbsp;' . kc($o['hodnota']) : e($o['masaz_nazev']);
    $pod = $o['typ'] === 'hodnota' ? 'na jakoukoli masáž ve studiu' : $o['delka'] . ' minut';
    $pod .= ' · platnost ' . cenik()['platnost_mesicu'] . '&nbsp;měsíců od vystavení';
    $cena = $sCenou ? '<td align="right" style="padding:24px 0 6px;font-family:' . F_SERIF . ';font-size:24px;line-height:30px;color:#1B211F;white-space:nowrap;">' . kc($o['cena_poukazu']) . '</td>' : '';
    $h = '<tr><td class="px" style="padding:32px 48px 0;">'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #CBBBA0;"><tr>'
        . '<td style="padding:24px 0 6px;font-family:' . F_SERIF . ';font-size:24px;line-height:30px;color:#1B211F;">' . $nazev . '</td>' . $cena . '</tr>'
        . '<tr><td colspan="2" style="padding:0 0 20px;font-family:' . F_SANS . ';font-size:14px;line-height:22px;color:#55605C;">' . $pod . '</td></tr></table>';
    if ($o['pro'] !== '' || $o['vzkaz'] !== '' || $o['od'] !== '') {
        $h .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#E4DCCC;"><tr><td style="padding:22px 24px;border-left:3px solid #304F47;">'
            . '<p style="margin:0 0 8px;font-family:' . F_SANS . ';font-size:11px;line-height:16px;letter-spacing:2px;text-transform:uppercase;color:#55605C;">Na poukazu bude</p>'
            . ($o['pro'] !== '' ? '<p style="margin:0 0 4px;font-family:' . F_SANS . ';font-size:15px;line-height:22px;color:#1B211F;">Pro <strong>' . e($o['pro']) . '</strong></p>' : '')
            . ($o['vzkaz'] !== '' ? '<p style="margin:0 0 10px;font-family:' . F_SERIF . ';font-style:italic;font-size:19px;line-height:28px;color:#304F47;">„' . nl2br(e($o['vzkaz'])) . '“</p>' : '')
            . ($o['od'] !== '' ? '<p style="margin:0;font-family:' . F_SANS . ';font-size:15px;line-height:22px;color:#1B211F;">Od <strong>' . e($o['od']) . '</strong></p>' : '')
            . '</td></tr></table>';
    }
    if (!$sDorucenim) return $h . '</td></tr>';
    $radky = [['Doručení', doruceni_text($o)], ['Datum doručení', '<strong>' . termin_text($o) . '</strong>']];
    if ($sCenou && $o['postovne'] > 0) $radky[] = ['Poštovné', kc($o['postovne'])];
    return $h . m_radky($radky) . '</td></tr>';
}

function doruceni_text(array $o): string
{
    if ($o['doruceni'] === 'email') return 'E-mailem jako PDF na&nbsp;' . e($o['kupujici']['email']);
    $a = $o['adresa'];
    return 'Poštou, tištěný poukaz<br>' . e($a['jmeno']) . ', ' . e($a['ulice']) . ', ' . e($a['psc']) . ' ' . e($a['mesto']);
}

function termin_text(array $o, bool $html = true): string
{
    $nb = $html ? '&nbsp;' : ' ';
    if ($o['doruceni'] === 'email') return $o['termin'] ? datum_cz($o['termin'], true) : 'hned po připsání platby';
    return $o['termin'] ? 'má dorazit do ' . datum_cz($o['termin'], true) : "do 3{$nb}pracovních dnů od připsání platby";
}

function m_kroky(string $nadpis, array $kroky): string
{
    $h = '<tr><td class="px" style="padding:40px 48px 8px;"><h2 style="margin:0 0 20px;font-family:' . F_SERIF . ';font-weight:400;font-size:24px;line-height:30px;color:#304F47;">' . $nadpis . '</h2>'
        . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">';
    foreach ($kroky as $i => $k) {
        $h .= '<tr><td width="44" valign="top" style="padding:0 0 18px;font-family:' . F_SERIF . ';font-size:20px;line-height:24px;color:#CBBBA0;">' . sprintf('%02d', $i + 1) . '</td>'
            . '<td valign="top" style="padding:0 0 18px;font-family:' . F_SANS . ';font-size:15px;line-height:24px;color:#1B211F;">' . $k . '</td></tr>';
    }
    return $h . '</table></td></tr>';
}

function m_podpis(): string
{
    return '<tr><td class="px" style="padding:8px 48px 40px;"><p style="margin:0 0 4px;font-family:' . F_SANS . ';font-size:15px;line-height:24px;color:#1B211F;">Kdyby cokoli, stačí odpovědět na&nbsp;tenhle e-mail.</p>'
        . '<p style="margin:0;font-family:' . F_SERIF . ';font-style:italic;font-size:20px;line-height:28px;color:#304F47;">Lukáš</p></td></tr>';
}

function m_drobne(): string
{
    return '<tr><td class="px" style="padding:20px 48px;border-top:1px solid #E4DCCC;"><p style="margin:0;font-family:' . F_SANS . ';font-size:12px;line-height:19px;color:#55605C;">'
        . 'Poukaz platí ' . cenik()['platnost_mesicu'] . '&nbsp;měsíců od vystavení a&nbsp;je přenosný. Na&nbsp;dražší masáž se doplácí rozdíl, za&nbsp;peníze se nevyměňuje. '
        . 'Od&nbsp;objednávky můžeš do&nbsp;14&nbsp;dnů od&nbsp;převzetí poukazu odstoupit, pokud k&nbsp;němu ještě není domluvený termín. Celé znění v&nbsp;'
        . '<a href="https://uvolnito.cz/pravni-informace.html#op-poukazy" style="color:#304F47;">obchodních podmínkách</a>.</p></td></tr>';
}

function m_foto_cid(array $o): array
{
    $c = cenik();
    $f = $o['typ'] === 'hodnota' ? $c['hodnota_foto']['email_foto'] : masaz($o['masaz_id'])['email_foto'];
    return ['foto' => LIB . '/obrazky/' . $f . '-1200x600.jpg'];
}

function m_zaklad_obrazky(): array
{
    return ['logo' => ROOT . '/assets/logo/ODVOZENO-wordmark-kremove.png'];
}

function m_tlacitko(string $href, string $text): string
{
    return '<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td style="background-color:#304F47;border-radius:999px;">'
        . '<a href="' . e($href) . '" style="display:inline-block;padding:14px 26px;font-family:' . F_SANS . ';font-size:14px;line-height:20px;color:#F7F3EC;text-decoration:none;font-weight:600;letter-spacing:.5px;">' . $text . '</a></td></tr></table>';
}

/* ════════════ 1. Zákazníkovi: objednávka přijata, zaplať ════════════ */
function email_objednavka_zakaznik(array $o): array
{
    $nazev = e(strip_tags(str_replace('&nbsp;', ' ', nazev_poukazu($o))));
    $platba = '<tr><td class="px" style="padding:36px 48px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#304F47;">'
        . '<tr><td style="padding:28px 28px 8px;"><p style="margin:0 0 6px;font-family:' . F_SANS . ';font-size:11px;line-height:16px;letter-spacing:2px;text-transform:uppercase;color:#CBBBA0;">Zbývá zaplatit převodem</p>'
        . '<p style="margin:0;font-family:' . F_SERIF . ';font-size:40px;line-height:46px;color:#F7F3EC;">' . kc($o['celkem']) . '</p></td></tr>'
        . '<tr><td style="padding:12px 28px 28px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td class="stack" valign="top">';
    foreach ([['Číslo účtu', e(cfg('ucet'))], ['Variabilní symbol', $o['vs']], ['Zpráva pro příjemce', 'Poukaz ' . $o['id']]] as [$k, $v]) {
        $platba .= '<p style="margin:8px 0 0;font-family:' . F_SANS . ';font-size:13px;line-height:18px;color:#CBBBA0;">' . $k . '</p>'
            . '<p style="margin:0 0 6px;font-family:' . F_SANS . ';font-size:17px;line-height:24px;color:#F7F3EC;font-weight:600;">' . $v . '</p>';
    }
    $platba .= '</td><td class="stack stack-gap" width="168" valign="top" align="right" style="padding-left:20px;">'
        . '<img src="cid:qr" width="148" height="148" alt="QR Platba" style="display:block;border:0;width:148px;height:148px;background:#fff;">'
        . '<p style="margin:8px 0 0;font-family:' . F_SANS . ';font-size:12px;line-height:17px;color:#CBBBA0;text-align:left;">Naskenuj v&nbsp;bankovní aplikaci</p>'
        . '</td></tr></table></td></tr></table></td></tr>';

    $kdy = termin_text($o);
    $kroky = ['Pošleš platbu. Převod obvykle dorazí do&nbsp;1–2&nbsp;pracovních dnů.'];
    if ($o['doruceni'] === 'email') {
        $kroky[] = $o['termin']
            ? '<strong>' . datum_cz($o['termin']) . '</strong> ti pošlu poukaz e-mailem jako PDF. Kdyby platba dorazila až po&nbsp;tomhle datu, pošlu ho hned, jak přijde.'
            : 'Jakmile platba dorazí, pošlu ti poukaz e-mailem jako PDF.';
    } else {
        $kroky[] = $o['termin']
            ? 'Poukaz vytisknu a&nbsp;odešlu poštou tak, aby dorazil do&nbsp;<strong>' . datum_cz($o['termin']) . '</strong>. Česká pošta přesné datum nezaručuje, proto počítám s&nbsp;rezervou.'
            : 'Po&nbsp;připsání platby poukaz vytisknu a&nbsp;do&nbsp;3&nbsp;pracovních dnů odešlu poštou.';
    }
    $kroky[] = 'S&nbsp;poukazem stačí zarezervovat termín online na&nbsp;<a href="https://uvolni-to.reservio.com" style="color:#304F47;">uvolni-to.reservio.com</a> a&nbsp;poukaz vzít s&nbsp;sebou.';

    $obsah = m_uvod('Objednávka ' . $o['id'], 'Díky, poukaz je skoro na&nbsp;cestě.',
            'Objednávku mám. Zbývá jen poslat platbu&nbsp;— jakmile dorazí, poukaz pošlu přesně podle objednávky. Níž najdeš rekapitulaci a&nbsp;platební údaje.')
        . m_rekap($o) . $platba . m_kroky('Co bude dál', $kroky) . m_podpis() . m_drobne();

    $text = "Díky za objednávku poukazu {$o['id']}.\n\n"
        . strip_tags(str_replace('&nbsp;', ' ', nazev_poukazu($o))) . "\n"
        . 'Doručení: ' . strip_tags(str_replace(['<br>', '&nbsp;'], [', ', ' '], doruceni_text($o))) . "\n"
        . 'Datum doručení: ' . termin_text($o, false) . "\n\n"
        . 'ZAPLATIT: ' . kc($o['celkem'], false) . "\nÚčet: " . cfg('ucet') . "\nVariabilní symbol: {$o['vs']}\nZpráva: Poukaz {$o['id']}\n\n"
        . "Kdyby cokoli, stačí odpovědět na tenhle e-mail.\nLukáš, uvolni.to — Skřivanova 335/5, Brno, +420 608 654 708";

    return [
        'predmet' => "Objednávka poukazu {$o['id']} — " . kc($o['celkem'], false),
        'html' => m_layout('Objednávka poukazu ' . $o['id'], $nazev . ' · ' . kc($o['celkem'], false) . ' · platební údaje a QR kód uvnitř.', $obsah, 'foto', ''),
        'text' => $text,
        'obrazky' => m_zaklad_obrazky() + m_foto_cid($o) + ['qr' => ['data' => qr_png($o), 'typ' => 'image/png']],
    ];
}

/* ════════════ 2. Lukášovi: nová objednávka ════════════ */
function email_objednavka_lukas(array $o, string $odkaz): array
{
    $s = F_SANS;
    $klic = function (string $k, string $v, string $pod, string $barva = '#1B211F') use ($s) {
        return '<td class="stack" width="33%" valign="top" style="padding:0 12px 16px 0;"><p style="margin:0 0 4px;font-family:' . $s . ';font-size:12px;color:#55605C;">' . $k . '</p>'
            . '<p style="margin:0;font-family:Georgia,serif;font-size:24px;line-height:30px;color:' . $barva . ';">' . $v . '</p>'
            . '<p style="margin:2px 0 0;font-family:' . $s . ';font-size:12px;color:#55605C;">' . $pod . '</p></td>';
    };
    $termin = $o['termin'] ? date('j. n.', strtotime($o['termin'])) : 'hned';
    $terminPod = $o['doruceni'] === 'email' ? 'e-mailem' : ($o['termin'] ? 'poštou, dorazit do' : 'poštou, do 3 prac. dnů');

    $radky = [
        ['Poukaz', '<strong>' . nazev_poukazu($o) . '</strong> — ' . kc($o['cena_poukazu'])],
        ['Pro koho', e($o['pro']) ?: '—'], ['Vzkaz', $o['vzkaz'] !== '' ? '<em>„' . nl2br(e($o['vzkaz'])) . '“</em>' : '—'], ['Od koho', e($o['od']) ?: '—'],
        ['Doručení', doruceni_text($o)], ['Datum doručení', '<strong>' . termin_text($o) . '</strong>'],
        ['Kupující', e($o['kupujici']['jmeno']) . '<br><a href="mailto:' . e($o['kupujici']['email']) . '">' . e($o['kupujici']['email']) . '</a>'
            . ($o['kupujici']['telefon'] !== '' ? '<br>' . e($o['kupujici']['telefon']) : '')],
    ];
    $obsah = '<tr><td class="px" style="padding:32px 40px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>'
        . $klic('Částka', kc($o['celkem']), $o['postovne'] ? kc($o['cena_poukazu']) . ' + poštovné' : 'bez poštovného')
        . $klic('Variabilní symbol', (string) $o['vs'], $o['id'])
        . $klic('Doručit', $termin, $terminPod, '#B5714E')
        . '</tr></table></td></tr>'
        . '<tr><td class="px" style="padding:8px 40px 0;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#FFFFFF;"><tr><td style="padding:18px 20px;font-family:' . $s . ';font-size:14px;line-height:22px;color:#1B211F;">'
        . '<strong>Co udělat:</strong> až v&nbsp;bance uvidíš platbu ' . kc($o['celkem']) . ' s&nbsp;VS&nbsp;' . $o['vs'] . ', klikni na&nbsp;tlačítko. Poukaz i&nbsp;doklad se vyrobí a&nbsp;'
        . ($o['doruceni'] === 'email' ? ($o['termin'] ? 'poukaz odejde zákazníkovi ' . datum_cz($o['termin']) . '.' : 'hned odejdou zákazníkovi.') : 'dostaneš PDF k&nbsp;vytištění a&nbsp;adresu.')
        . '<br><br>' . m_tlacitko($odkaz, 'Platba přišla — vystavit poukaz') . '</td></tr></table></td></tr>'
        . '<tr><td class="px" style="padding:24px 40px 36px;">' . m_radky($radky) . '</td></tr>';

    return [
        'predmet' => "Nová objednávka poukazu {$o['id']} — " . kc($o['celkem'], false),
        'html' => m_layout('Nová objednávka ' . $o['id'], 'VS ' . $o['vs'] . ' · ' . kc($o['celkem'], false) . ' · ' . strip_tags(termin_text($o)), $obsah),
        'text' => "Nová objednávka {$o['id']}\nČástka: " . kc($o['celkem'], false) . "\nVS: {$o['vs']}\nPoukaz: " . strip_tags(str_replace('&nbsp;', ' ', nazev_poukazu($o)))
            . "\nDatum doručení: " . termin_text($o, false) . "\nKupující: {$o['kupujici']['jmeno']}, {$o['kupujici']['email']}\n\nPlatba přišla? Otevři: $odkaz",
        'obrazky' => m_zaklad_obrazky(),
    ];
}

/* ════════════ 3. Zákazníkovi: tady je poukaz (PDF v příloze) ════════════ */
function email_poukaz_zakaznik(array $o): array
{
    $obsah = m_uvod('Poukaz ' . $o['id'], 'Tady je tvůj poukaz.',
            'Poukaz najdeš v&nbsp;příloze jako PDF&nbsp;— můžeš ho vytisknout, nebo rovnou přeposlat. Doklad o&nbsp;zaplacení je v&nbsp;samostatné příloze, ať ho obdarovaný nevidí.')
        . m_rekap($o, false, false)
        . m_kroky('Jak poukaz uplatnit', [
            'Termín se rezervuje online na&nbsp;<a href="https://uvolni-to.reservio.com" style="color:#304F47;">uvolni-to.reservio.com</a> nebo na&nbsp;telefonu +420&nbsp;608&nbsp;654&nbsp;708.',
            'Při rezervaci stačí zmínit číslo poukazu <strong>' . $o['id'] . '</strong>.',
            'Poukaz platí do&nbsp;<strong>' . datum_cz($o['platnost_do']) . '</strong>.',
        ]) . m_podpis() . m_drobne();
    return [
        'predmet' => 'Tvůj dárkový poukaz ' . $o['id'],
        'html' => m_layout('Dárkový poukaz ' . $o['id'], 'Poukaz v příloze, platí do ' . datum_cz($o['platnost_do']) . '.', $obsah, 'foto'),
        'text' => "Tady je tvůj dárkový poukaz {$o['id']} (PDF v příloze), platí do " . datum_cz($o['platnost_do']) . ".\nRezervace: uvolni-to.reservio.com nebo +420 608 654 708.\n\nLukáš, uvolni.to",
        'obrazky' => m_zaklad_obrazky() + m_foto_cid($o),
    ];
}

/* ════════════ 4. Zákazníkovi: platba dorazila, poukaz přijde později ════════════ */
function email_zaplaceno_zakaznik(array $o): array
{
    $kdy = $o['doruceni'] === 'email'
        ? 'Poukaz ti pošlu e-mailem <strong>' . datum_cz($o['termin'], true) . '</strong>, přesně podle objednávky.'
        : ($o['termin']
            ? 'Poukaz vytisknu a&nbsp;odešlu poštou tak, aby dorazil do&nbsp;<strong>' . datum_cz($o['termin'], true) . '</strong>.'
            : 'Poukaz vytisknu a&nbsp;do&nbsp;3&nbsp;pracovních dnů odešlu poštou.');
    $obsah = m_uvod('Objednávka ' . $o['id'], 'Platba dorazila, díky.', $kdy . ' Doklad o&nbsp;zaplacení posílám v&nbsp;příloze.')
        . m_rekap($o, false) . m_podpis() . m_drobne();
    return [
        'predmet' => 'Platba za poukaz ' . $o['id'] . ' dorazila',
        'html' => m_layout('Platba dorazila', 'Doklad v příloze. Poukaz: ' . strip_tags(termin_text($o)) . '.', $obsah, 'foto'),
        'text' => "Platba za poukaz {$o['id']} dorazila, díky. " . strip_tags(str_replace('&nbsp;', ' ', $kdy)) . "\nDoklad v příloze.\n\nLukáš, uvolni.to",
        'obrazky' => m_zaklad_obrazky() + m_foto_cid($o),
    ];
}

/* ════════════ 5. Lukášovi: vytiskni a odešli poštou ════════════ */
function email_posta_lukas(array $o): array
{
    $a = $o['adresa'];
    $odeslat = $o['termin']
        ? 'Odeslat nejpozději <strong>' . datum_cz(pracovni_dny_zpet($o['termin'], 2), true) . '</strong> (má dorazit do ' . datum_cz($o['termin']) . ')'
        : 'Odeslat do&nbsp;<strong>3&nbsp;pracovních dnů</strong> (slíbeno zákazníkovi)';
    $obsah = m_uvod('Poukaz ' . $o['id'], 'Vytiskni a&nbsp;odešli poštou', 'Poukaz je v&nbsp;příloze jako PDF ve&nbsp;formátu A5. ' . $odeslat . '.')
        . '<tr><td class="px" style="padding:16px 48px 40px;">' . m_radky([
            ['Adresa', '<strong>' . e($a['jmeno']) . '<br>' . e($a['ulice']) . '<br>' . e($a['psc']) . ' ' . e($a['mesto']) . '</strong>'],
            ['Poukaz', nazev_poukazu($o)], ['Kupující', e($o['kupujici']['jmeno']) . ', ' . e($o['kupujici']['email'])],
        ]) . '</td></tr>';
    return [
        'predmet' => "Poslat poštou: poukaz {$o['id']} — {$a['jmeno']}",
        'html' => m_layout('Poslat poštou ' . $o['id'], strip_tags($odeslat), $obsah),
        'text' => "Vytiskni a odešli poukaz {$o['id']}.\n" . strip_tags(str_replace('&nbsp;', ' ', $odeslat)) . "\nAdresa: {$a['jmeno']}, {$a['ulice']}, {$a['psc']} {$a['mesto']}",
        'obrazky' => m_zaklad_obrazky(),
    ];
}
