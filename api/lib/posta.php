<?php
/*
 * Odesílání e-mailů. mail_mode 'log' = nic neodchází, zpráva se uloží do data/posta/
 * (HTML + přílohy) a jde si ji prohlédnout přes api/dev-posta.php.
 */
declare(strict_types=1);

require_once LIB . '/vendor/PHPMailer/Exception.php';
require_once LIB . '/vendor/PHPMailer/PHPMailer.php';
require_once LIB . '/vendor/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * @param array $zprava [
 *   'komu' => adresa, 'jmeno' => ?, 'predmet', 'html', 'text',
 *   'reply_to' => ?adresa,
 *   'obrazky' => [cid => cesta k souboru | ['data' => binární, 'typ' => mime]],
 *   'prilohy' => [nazev souboru => binární data (PDF)],
 * ]
 */
function posli(array $zprava): bool
{
    if (cfg('mail_mode') === 'log') return uloz_do_logu($zprava);

    $m = new PHPMailer(true);
    try {
        $s = cfg('smtp');
        $m->isSMTP();
        $m->Host = $s['host']; $m->Port = (int) $s['port'];
        $m->SMTPAuth = true; $m->Username = $s['user']; $m->Password = $s['pass'];
        $m->SMTPSecure = $s['secure'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $m->CharSet = 'UTF-8';
        $m->setLanguage('cs');

        [$od, $odJmeno] = cfg('odesilatel');
        $m->setFrom($od, $odJmeno);
        $m->addAddress($zprava['komu'], $zprava['jmeno'] ?? '');
        if (!empty($zprava['reply_to'])) $m->addReplyTo($zprava['reply_to']);

        $m->Subject = $zprava['predmet'];
        $m->isHTML(true);
        $m->Body = $zprava['html'];
        $m->AltBody = $zprava['text'];
        foreach ($zprava['obrazky'] ?? [] as $cid => $zdroj) {
            if (is_array($zdroj)) $m->addStringEmbeddedImage($zdroj['data'], $cid, $cid . '.png', 'base64', $zdroj['typ']);
            else $m->addEmbeddedImage($zdroj, $cid);
        }
        foreach ($zprava['prilohy'] ?? [] as $nazev => $data) $m->addStringAttachment($data, $nazev, 'base64', 'application/pdf');
        $m->send();
        return true;
    } catch (Throwable $e) {
        loguj('E-mail se neodeslal (' . $zprava['predmet'] . '): ' . $m->ErrorInfo . ' ' . $e->getMessage());
        return false;
    }
}

function uloz_do_logu(array $z): bool
{
    $dir = data_dir('posta') . '/' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
    mkdir($dir, 0750, true);
    $html = $z['html'];
    // cid: obrázky přepíšeme na soubory vedle zprávy, ať jde náhled otevřít v prohlížeči
    foreach ($z['obrazky'] ?? [] as $cid => $zdroj) {
        $soubor = "$cid.png";
        file_put_contents("$dir/$soubor", is_array($zdroj) ? $zdroj['data'] : file_get_contents($zdroj));
        $html = str_replace("cid:$cid", $soubor, $html);
    }
    foreach ($z['prilohy'] ?? [] as $nazev => $data) file_put_contents("$dir/" . basename($nazev), $data);
    file_put_contents("$dir/zprava.html", $html);
    file_put_contents("$dir/zprava.txt", $z['text']);
    file_put_contents("$dir/hlavicka.json", json_encode([
        'komu' => $z['komu'], 'predmet' => $z['predmet'], 'reply_to' => $z['reply_to'] ?? null,
        'prilohy' => array_keys($z['prilohy'] ?? []), 'cas' => date('c'),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return true;
}
