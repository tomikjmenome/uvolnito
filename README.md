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
- [ ] rozhodnout, jestli zapnout zpět podstránky masáží

## Licence

Obsah, fotografie a texty patří klientovi — nejsou volně použitelné.
Písma Fraunces a Inter v `assets/fonts/` jsou pod SIL Open Font License 1.1.
