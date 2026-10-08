<?php
/*
 * Dokumenty k poukazu: QR Platba (PNG), samotný poukaz (PDF A5 na šířku)
 * a doklad o zaplacení (PDF A4).
 *
 * Poukaz se kreslí jako obrázek přes GD — fotka masáže pod tmavým závojem a text
 * ve Fraunces/Inter. tFPDF ho pak jen vloží na celou stránku. Díky tomu vypadá
 * PDF přesně jako náhled ve formuláři a nezáleží na tom, čím ho kdo otevře.
 */
declare(strict_types=1);

require_once LIB . '/vendor/phpqrcode.php';
require_once LIB . '/vendor/tfpdf/tfpdf.php';

const FONTY = LIB . '/vendor/tfpdf/font/unifont/';

/* ═══ QR Platba ═══ */
function qr_png(array $o): string
{
    ob_start();
    QRcode::png(spayd($o), false, QR_ECLEVEL_M, 8, 2);
    return (string) ob_get_clean();
}

/* ═══ Poukaz — obrázek ═══ */
function poukaz_foto(array $o): array
{
    $c = cenik();
    $f = $o['typ'] === 'hodnota' ? $c['hodnota_foto'] : masaz($o['masaz_id']);
    return [ROOT . '/assets/foto/opt/' . $f['foto'] . '-' . $f['foto_jpg'] . '.jpg', (float) $f['pozice']];
}

function barva($im, string $hex, int $alpha = 0)
{
    [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
    return imagecolorallocatealpha($im, $r, $g, $b, $alpha);
}

/* text s prostrkáním písmen (kapitálky na štítcích) — GD to samo neumí */
function text_prostrkany($im, float $vel, int $x, int $y, $barva, string $font, string $s, float $mezera): void
{
    foreach (mb_str_split($s) as $ch) {
        imagettftext($im, $vel, 0, $x, $y, $barva, $font, $ch);
        $bb = imagettfbbox($vel, 0, $font, $ch);
        $x += (int) round($bb[2] - $bb[0] + $mezera);
    }
}

/* zalomí text do šířky; vrací pole řádků */
function zalom(string $s, float $vel, string $font, int $sirka): array
{
    $radky = [];
    foreach (preg_split('/\R/u', $s) as $odst) {
        $radek = '';
        foreach (preg_split('/\s+/u', trim($odst)) as $slovo) {
            $zkus = $radek === '' ? $slovo : "$radek $slovo";
            $bb = imagettfbbox($vel, 0, $font, $zkus);
            if ($bb[2] - $bb[0] > $sirka && $radek !== '') { $radky[] = $radek; $radek = $slovo; }
            else $radek = $zkus;
        }
        $radky[] = $radek;
    }
    return $radky;
}

function poukaz_obrazek(array $o): GdImage
{
    $W = 2100; $H = 1480;                                   // poměr A5 na šířku (~200 dpi)
    $im = imagecreatetruecolor($W, $H);
    imagealphablending($im, true);

    // fotka „cover“ s ohniskem na svislé pozici
    [$soubor, $pozice] = poukaz_foto($o);
    $f = imagecreatefromjpeg($soubor);
    $fw = imagesx($f); $fh = imagesy($f);
    $vyrez_h = (int) round($fw * $H / $W);
    $y0 = (int) max(0, min($fh - $vyrez_h, $pozice * $fh - $vyrez_h / 2));
    imagecopyresampled($im, $f, 0, 0, 0, $y0, $W, $H, $fw, $vyrez_h);
    imagedestroy($f);

    // závoj: zleva tmavý (text), doprava fotka prosvítá; dole pruh pro údaje
    for ($x = 0; $x < $W; $x++) {
        $p = $x / $W;
        $kryti = $p < .55 ? .90 - $p * .45 : max(.38, .65 - ($p - .55) * .6);   // 0..1
        imageline($im, $x, 0, $x, $H, barva($im, '#223A34', (int) round(127 * (1 - $kryti))));
    }
    for ($y = $H - 330; $y < $H; $y++) {
        $p = ($y - ($H - 330)) / 330;
        imageline($im, 0, $y, $W, $y, barva($im, '#223A34', (int) round(127 * (1 - $p * .55))));
    }

    $krem = barva($im, '#F7F3EC'); $pisek = barva($im, '#CBBBA0'); $pisek2 = barva($im, '#CBBBA0', 70);
    $FR = FONTY . 'Fraunces72pt-Light.ttf'; $FI = FONTY . 'Fraunces72pt-LightItalic.ttf';
    $IL = FONTY . 'Inter-Light.ttf'; $IM = FONTY . 'Inter-Medium.ttf';
    $X = 150;

    // logo
    $logo = imagecreatefrompng(ROOT . '/assets/logo/ODVOZENO-wordmark-kremove.png');
    imagecopyresampled($im, $logo, $X, 130, 0, 0, 300, (int) round(300 * imagesy($logo) / imagesx($logo)), imagesx($logo), imagesy($logo));
    imagedestroy($logo);

    // štítek + čára
    text_prostrkany($im, 22, $X, 400, $pisek, $IM, 'DÁRKOVÝ POUKAZ', 9);
    imageline($im, $X, 440, $X + 120, 440, $pisek);

    // hlavní nadpis
    $y = 590;
    if ($o['typ'] === 'hodnota') {
        imagettftext($im, 120, 0, $X, $y, $krem, $FR, kc($o['hodnota'], false));
        imagettftext($im, 34, 0, $X, $y + 85, $pisek, $IL, 'na jakoukoli masáž ve studiu');
    } else {
        foreach (zalom($o['masaz_nazev'], 104, $FR, 1150) as $i => $r) imagettftext($im, 104, 0, $X, $y + $i * 135, $krem, $FR, $r);
        $y += (count(zalom($o['masaz_nazev'], 104, $FR, 1150)) - 1) * 135;
        imagettftext($im, 34, 0, $X, $y + 85, $pisek, $IL, $o['delka'] . ' minut');
    }
    $y += 210;

    // věnování
    if ($o['pro'] !== '') { imagettftext($im, 32, 0, $X, $y, $krem, $IL, 'Pro ' . $o['pro']); $y += 80; }
    if ($o['vzkaz'] !== '') {
        foreach (array_slice(zalom('„' . $o['vzkaz'] . '“', 40, $FI, 1100), 0, 4) as $r) {
            imagettftext($im, 40, 0, $X, $y, $krem, $FI, $r); $y += 66;
        }
        $y += 14;
    }
    if ($o['od'] !== '') imagettftext($im, 32, 0, $X, $y, $krem, $IL, 'Od ' . $o['od']);

    // spodní pruh s údaji
    $yb = $H - 120;
    imageline($im, $X, $yb - 80, $W - $X, $yb - 80, $pisek2);
    $sloupce = [
        ['ČÍSLO POUKAZU', $o['id']],
        ['PLATÍ DO', datum_cz($o['platnost_do'])],
        ['REZERVACE', 'uvolni-to.reservio.com'],
        ['STUDIO', 'Skřivanova 335/5, Brno · +420 608 654 708'],
    ];
    $xs = [$X, $X + 330, $X + 660, $X + 1080];
    foreach ($sloupce as $i => [$k, $v]) {
        text_prostrkany($im, 15, $xs[$i], $yb - 20, $pisek, $IM, $k, 5);
        imagettftext($im, 23, 0, $xs[$i], $yb + 30, $krem, $IL, $v);
    }
    return $im;
}

/* ═══ Poukaz — PDF ═══ */
function poukaz_pdf(array $o): string
{
    $im = poukaz_obrazek($o);
    $tmp = tempnam(sys_get_temp_dir(), 'pk') . '.jpg';
    imagejpeg($im, $tmp, 90);
    imagedestroy($im);

    $pdf = new tFPDF('L', 'mm', 'A5');
    $pdf->SetTitle('Dárkový poukaz ' . $o['id'], true);
    $pdf->SetAuthor('uvolni.to — masáže', true);
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false);
    $pdf->AddPage();
    $pdf->Image($tmp, 0, 0, 210, 148);
    unlink($tmp);
    return $pdf->Output('S');
}

/* ═══ Doklad o zaplacení — PDF ═══
   Neplátce DPH: stačí identifikace prodávajícího, číslo, datum, předmět a částka. */
function doklad_pdf(array $o): string
{
    $pdf = new tFPDF('P', 'mm', 'A4');
    $pdf->SetTitle('Doklad ' . $o['doklad_cislo'], true);
    $pdf->SetAuthor('Lukáš Václavek', true);
    $pdf->AddFont('Inter', '', 'Inter-Regular.ttf', true);
    $pdf->AddFont('Inter', 'L', 'Inter-Light.ttf', true);
    $pdf->AddFont('Inter', 'B', 'Inter-Medium.ttf', true);
    $pdf->AddFont('Fraunces', '', 'Fraunces72pt-Light.ttf', true);
    $pdf->SetMargins(22, 22, 22);
    $pdf->AddPage();
    $pdf->Image(ROOT . '/assets/logo/ODVOZENO-wordmark-barevne.png', 22, 20, 40);

    $pdf->SetTextColor(48, 79, 71);
    $pdf->SetFont('Fraunces', '', 24);
    $pdf->SetXY(22, 42);
    $pdf->Cell(0, 10, 'Doklad o zaplacení', 0, 1);
    $pdf->SetFont('Inter', '', 10);
    $pdf->SetTextColor(85, 96, 92);
    $pdf->Cell(0, 6, 'č. ' . $o['doklad_cislo'], 0, 1);
    $pdf->Ln(8);

    $blok = function (string $nadpis, array $radky, float $x, float $y) use ($pdf) {
        $pdf->SetXY($x, $y);
        $pdf->SetFont('Inter', 'B', 8);
        $pdf->SetTextColor(74, 110, 100);
        $pdf->Cell(80, 5, mb_strtoupper($nadpis), 0, 2);
        $pdf->SetFont('Inter', '', 10);
        $pdf->SetTextColor(27, 33, 31);
        foreach ($radky as $r) $pdf->Cell(80, 5.4, $r, 0, 2);
    };
    $y = $pdf->GetY();
    $blok('Prodávající', ['Lukáš Václavek — uvolni.to', 'Skřivanova 335/5, 602 00 Brno', 'IČO: 19696884', 'Nejsem plátce DPH.',
        'Zapsán v živnostenském rejstříku', 'Živnostenského úřadu města Brna'], 22, $y);
    $kup = [$o['kupujici']['jmeno'], $o['kupujici']['email']];
    if ($o['doruceni'] === 'posta') { $kup[] = $o['adresa']['ulice']; $kup[] = $o['adresa']['psc'] . ' ' . $o['adresa']['mesto']; }
    $blok('Kupující', $kup, 112, $y);

    $pdf->SetY($y + 44);
    $pdf->SetFont('Inter', '', 10);
    foreach ([
        ['Datum vystavení', datum_cz($o['zaplaceno_dne'])],
        ['Datum přijetí platby', datum_cz($o['zaplaceno_dne'])],
        ['Způsob úhrady', 'bankovním převodem, VS ' . $o['vs']],
        ['Objednávka', $o['id']],
    ] as [$k, $v]) {
        $pdf->SetTextColor(85, 96, 92); $pdf->Cell(50, 6.5, $k);
        $pdf->SetTextColor(27, 33, 31); $pdf->Cell(0, 6.5, $v, 0, 1);
    }

    // položky
    $pdf->Ln(8);
    $pdf->SetDrawColor(203, 187, 160);
    $pdf->SetFont('Inter', 'B', 8); $pdf->SetTextColor(74, 110, 100);
    $pdf->Cell(130, 8, 'POLOŽKA', 'B'); $pdf->Cell(0, 8, 'CENA', 'B', 1, 'R');
    $pdf->SetFont('Inter', '', 10); $pdf->SetTextColor(27, 33, 31);
    $polozky = [['Dárkový poukaz — ' . nazev_poukazu($o) . ' (č. ' . $o['id'] . ')', $o['cena_poukazu']]];
    if ($o['postovne'] > 0) $polozky[] = ['Poštovné', $o['postovne']];
    foreach ($polozky as [$n, $c]) { $pdf->Cell(130, 9, $n, 'B'); $pdf->Cell(0, 9, kc($c, false), 'B', 1, 'R'); }
    $pdf->SetFont('Inter', 'B', 11);
    $pdf->Cell(130, 11, 'Celkem zaplaceno'); $pdf->Cell(0, 11, kc($o['celkem'], false), 0, 1, 'R');

    $pdf->Ln(10);
    $pdf->SetFont('Inter', 'L', 9); $pdf->SetTextColor(85, 96, 92);
    $pdf->MultiCell(0, 5, 'Poukaz platí do ' . datum_cz($o['platnost_do']) . ' a lze ho uplatnit ve studiu uvolni.to, Skřivanova 335/5, Brno. '
        . 'Podmínky poukazů: uvolnito.cz/pravni-informace.html#op-poukazy');
    return $pdf->Output('S');
}
