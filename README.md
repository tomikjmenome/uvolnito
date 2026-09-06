# uvolni.to — web masážního studia

Statický web pro masážní studio **uvolni.to** (Lukáš Václavek, Brno).
Jedna stránka `index.html`, žádný build — otevře se rovnou v prohlížeči.

## Stav

**Rozpracované.** Web je funkční, ale ještě není připravený nahradit
ostrý web na uvolnitomasaze.cz. Viz checklist níže.

## Struktura

```
index.html              hlavní stránka
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

## Lokální náhled

Reservio pouští svůj rezervační kalendář do `<iframe>` jen ze stránky
načtené přes **https**. Z `file://` ani `http://localhost` se rám nezobrazí.

```bash
python3 -m http.server 8000          # stačí na všechno kromě kalendáře
```

Pro plný náhled včetně kalendáře je potřeba https (self-signed certifikát stačí).

## Před ostrým spuštěním

- [ ] **`robots.txt` přepnout zpět na `Allow: /`** — teď zakazuje indexaci, aby
      náhled nekonkuroval ostrému webu ve vyhledávání
- [ ] doplnit chybějící údaje (v kódu označené třídou `.doplnit`): parkování,
      benefitní programy, víkendová otevírací doba, vzdělání a certifikace
- [ ] ověřit čísla „5 let praxe" a „1 200+ odmasírovaných hodin"
- [ ] vlastní stránka Obchodní podmínky & GDPR — teď odkazuje na starý WordPress.
      Web nově načítá Mapy Google, což do zásad patří zmínit
- [ ] `og:image` vyměnit za záběr na šířku (v `assets/foto/` leží dva 1920×1080)
- [ ] doplnit `canonical`, `sitemap.xml` a `robots.txt` o finální doménu
- [ ] rozhodnout, jestli zapnout zpět podstránky masáží

## Licence

Obsah, fotografie a texty patří klientovi — nejsou volně použitelné.
Písma Fraunces a Inter v `assets/fonts/` jsou pod SIL Open Font License 1.1.
