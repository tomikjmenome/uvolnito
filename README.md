# uvolni.to — web masážního studia

Statický web pro masážní studio **uvolni.to** (Lukáš Václavek, Brno).
Jedna stránka `index.html`, žádný build — otevře se rovnou v prohlížeči.

## Stav

**Nasazeno** na https://uvolnito.cz/ (WEDOS webhosting, 13. 9. 2026).
Stará doména uvolnitomasaze.cz zatím běží na původním WordPressu — přesměrování
na novou doménu je v plánu. Zbývající úkoly viz checklist níže.

## Struktura

```
index.html              hlavní stránka
pravni-informace.html   obchodní podmínky, ochrana údajů, cookies
.htaccess               přesměrování na https + bez www, cache, hlavičky
masaz-*.html            detaily masáží — DOČASNĚ ODPOJENÉ (noindex, index.html na ně neodkazuje)
uvolni-to.vcf           vizitka ke stažení (vCard 3.0)
assets/foto/            fotky studia (originály)
assets/foto/opt/        odvozené varianty WebP/JPEG pro <picture>
assets/fonts/           self-hostované Fraunces + Inter (SIL OFL 1.1)
assets/logo/            logo a favicony
CLAUDE.md               designové tokeny a pravidla projektu
PROJEKT.md              rešerše předloh a zadání
ANALYZA-C.md            analýza varianty C
SUPERPROMPT.md          zpřísňující pravidla pro varianty G+
```

## Dárkové poukazy (objednávka online)

`poukazy.html` + PHP v `api/`. Celý tok:

1. Zákazník vyplní formulář → `api/poukaz.php` ověří data, **spočítá cenu z `assets/data/poukazy-cenik.json`**
   (jediné místo s cenami), přidělí číslo `UT-RRRR-NNNN` (= VS `RRRRNNNN`) a pošle dva e-maily:
   zákazníkovi platební údaje + QR Platbu, Lukášovi objednávku s tlačítkem.
2. Lukáš v bance vidí platbu → v e-mailu klikne **„Platba přišla — vystavit poukaz“** (`api/potvrdit.php`,
   akce se provede až po potvrzení na stránce, ne samotným otevřením odkazu).
3. Vystaví se doklad (neplátce DPH, řada `RRRR-NNNN`) a poukaz v PDF (A5, fotka masáže):
   - e-mail „hned“ → poukaz + doklad odejdou zákazníkovi hned,
   - e-mail „k datu“ → zákazník dostane doklad, poukaz odešle `api/cron.php` v daný den,
   - pošta → zákazník dostane doklad, Lukáš PDF k tisku a adresu (s termínem „odeslat nejpozději“).

Data objednávek: `/poukazy-data/` vedle `/www` (když jde vytvořit), jinak `api/data/` (chráněno `.htaccess`).
Přehled pro Excel: `objednavky.csv` v datové složce.

**Lokálně:** `php -S 0.0.0.0:4000` v kořeni webu. `api/config.php` s `'mail_mode' => 'log'` a `'dev' => true`
→ e-maily se neodesílají, náhled je na `http://localhost:4000/api/dev-posta.php`.

**Nasazení na WEDOS** (zatím NEnasazovat — viz checklist):
- nahrát `poukazy.html`, `api/` (bez `api/data/` a bez lokálního `api/config.php`), `assets/data/`,
  `assets/js/poukazy.js`, `assets/css/podstranka.css`, `assets/js/podstranka.js`
- na serveru vytvořit `config.php` podle `api/config.example.php` (ideálně `/poukazy-data/config.php` mimo `/www`):
  `mail_mode` `smtp`, údaje schránky, `cron_klic`, `dev` `false`
- složka `api/lib/vendor/tfpdf/font/unifont/` musí být zapisovatelná (tFPDF si tam při prvním PDF uloží metriky písem)
- WEDOS → Plánované úlohy: denně ráno `https://uvolnito.cz/api/cron.php?klic=…`
- zapnout DKIM pro uvolnito.cz, otestovat na mail-tester.com
- PHP 8.1+ s rozšířeními gd, mbstring, openssl (WEDOS má)

Knihovny v `api/lib/vendor/` (bez Composeru): PHPMailer 6.9.3 (LGPL 2.1), tFPDF/FPDF (volná licence),
PHP QR Code (LGPL 3), písma Fraunces a Inter (SIL OFL 1.1) — licence jsou vedle souborů.

## Odkud jsou data

Ceny, délky masáží, otevírací doba a recenze jsou převzaté z rezervačního
systému klienta (`uvolni-to.reservio.com`), stav k 6. 9. 2026. **Nejsou
vymyšlené a nesmí se upravovat bez ověření u klienta.** Recenze jsou
doslovné citace z veřejného profilu včetně jmen a dat.

## Nasazení

Statické soubory se nahrávají přes FTPS do `/www` na WEDOS hostingu. Nahrává se
jen to, co web používá: `*.html`, `robots.txt`, `sitemap.xml`, `uvolni-to.vcf`,
`.htaccess` a `assets/{autor,css,fonts,foto/opt,js,logo,og}`. Originály fotek,
`*.md` a `nastroje/` na server nepatří. WEDOS vyžaduje FTPS s opakovaným použitím
TLS relace — curl na Windows to neumí, Python `ftplib` s předaným `session` ano.

## Lokální náhled

Reservio pouští svůj rezervační kalendář do `<iframe>` jen ze stránky
načtené přes **https**. Z `file://` ani `http://localhost` se rám nezobrazí.

```bash
python3 -m http.server 8000          # stačí na všechno kromě kalendáře
```

Pro plný náhled včetně kalendáře je potřeba https (self-signed certifikát stačí).

## Co ještě zbývá

- [ ] doplnit chybějící údaje (v kódu označené třídou `.doplnit`): parkování,
      benefitní programy, víkendová otevírací doba, vzdělání a certifikace
- [ ] ověřit čísla „5 let praxe" a „1 200+ odmasírovaných hodin"
- [ ] `og:image` vyměnit za záběr na šířku (v `assets/foto/` leží dva 1920×1080)
- [ ] přesměrovat starou doménu uvolnitomasaze.cz (301) na uvolnito.cz
- [ ] rozhodnout o e-mailu — kontakt zatím používá info@uvolnitomasaze.cz
- [ ] **dárkové poukazy** — lokálně hotové a otestované, NENASAZENO. Před spuštěním:
      schránka `poukazy@uvolnito.cz` na WEDOSu (nebo heslo aplikace Gmailu — nikdy hlavní heslo),
      config na serveru, cron, DKIM, test na mail-tester.com, sundat `noindex` z `poukazy.html`,
      doplnit do `pravni-informace.html`: online objednávka poukazu v §6 (platba převodem, poštovné 99 Kč,
      odeslání do 3 pracovních dnů) a účel „vyřízení objednávky poukazu“ v ochraně osobních údajů;
      ještě rozhodnout druh dopisu (obyčejný / doporučený). `index.html` už na poukazy odkazuje → nahrát spolu.
- [ ] **podstránky masáží** (`relaxacni-masaz.html`, `regeneracni-masaz.html`, `masaz-lavovymi-kameny.html`,
      `maderoterapie.html`, `mangovy-ritual.html`, `rychle-uvolneni.html`) jsou lokálně hotové a homepage
      na ně odkazuje, ale **obsahují lorem ipsum a mají `noindex` — NENAHRÁVAT**, dokud Lukáš nedodá texty.
      Při nasazení: zdroj je `navrhy-podstranky/_generuj.py`, pak sundat `noindex`, přidat do `sitemap.xml`,
      v `.htaccess` schovat příponu `.html` (canonical už míří na adresy bez přípony),
      nahrát i `assets/css/podstranka.css` a `assets/js/podstranka.js`, OG obrázky z `navrhy-podstranky/og/` do `assets/og/`.
      Pozor: změněný `index.html` (karty a patička vedou na podstránky) se smí nahrát až spolu s nimi.
- [ ] sjednotit otevírací dobu — web má Po–St 12–22, Čt 9–19, Pá 9–18, Reservio (2. 10. 2026)
      Po–Pá 8–22, So–Ne 9–20; po potvrzení od Lukáše opravit homepage, JSON-LD i podstránky
- [ ] automaticky aktualizovat počet recenzí z Reservia (dnes 32, web uvádí 24): PHP skript
      spouštěný denně cronem na WEDOS přečte `"reviewCount"` a `"rating"` ze stránky
      uvolni-to.reservio.com, uloží `recenze.json`, stránka v HTML drží záložní číslo a JS ho
      přepíše; kontrola věrohodnosti + e-mail při opakovaném selhání; zvážit oficiální Reservio API
      (klíč z Lukášova účtu); Google recenze jen přes Places API, jinak uvádět zvlášť

## Licence

Obsah, fotografie a texty patří klientovi — nejsou volně použitelné.
Písma Fraunces a Inter v `assets/fonts/` jsou pod SIL Open Font License 1.1.
