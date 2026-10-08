<?php
/*
 * Vzor konfigurace poukazů. Zkopírovat jako config.php (NE do gitu — je v .gitignore)
 * a vyplnit. Pokud to hosting dovolí, dát config.php i data MIMO /www:
 *   /poukazy-data/config.php   (o úroveň výš než /www — skript ho najde sám)
 */
return [
    // 'log' = e-maily se neodesílají, ukládají se do data/posta/ (vývoj, test)
    // 'smtp' = ostré odesílání
    'mail_mode' => 'log',

    'smtp' => [
        'host'   => 'wes1-smtp.wedos.net',   // OVĚŘIT v administraci WEDOS u schránky
        'port'   => 587,
        'secure' => 'tls',                   // 'tls' (587) nebo 'ssl' (465)
        'user'   => 'poukazy@uvolnito.cz',
        'pass'   => '',                      // heslo schránky — vyplnit jen na serveru
    ],

    'odesilatel'       => ['poukazy@uvolnito.cz', 'uvolni.to — masáže'],
    'kopie_objednavek' => 'info@uvolnitomasaze.cz',   // kam chodí objednávky Lukášovi
    'odpovedi_na'      => 'info@uvolnitomasaze.cz',   // Reply-To v e-mailech zákazníkům

    'ucet'  => '1594284008/0100',                     // Komerční banka
    'web'   => 'https://uvolnito.cz',                 // bez lomítka na konci — odkazy v e-mailech

    // náhodný řetězec (např. bin2hex(random_bytes(16))) — chrání api/cron.php volané z WEDOS cronu
    'cron_klic' => '',

    // true jen lokálně: zpřístupní api/dev-posta.php s náhledem uložených e-mailů
    'dev' => false,
];
