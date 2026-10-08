# SUPERPROMPT — podstránka Dárkové poukazy (objednávka online)

> **Stav 4. 10. 2026: postaveno a otestováno lokálně** (`poukazy.html`, `api/`, popis toku v `README.md`).
> Oproti plánu: hodnotové poukazy 1 000 / 2 000 Kč; poukaz vždy kupujícímu; e-mail k přesnému dni,
> pošta „co nejdřív (do 3 prac. dnů)“ nebo „naplánovat doručení“; vystavení jedním kliknutím Lukáše.
> Zbývá: schránka pro odesílání, nasazení, úprava obchodních podmínek, druh dopisu.

Závazné zadání pro stránku `poukazy.html` a její serverovou část.
Nadřazené dokumenty: `CLAUDE.md` (tokeny, typografie, technika), `SUPERPROMPT.md` (motion, autokontrola),
`pravni-informace.html` §6 (podmínky poukazů) a §9 (odstoupení). Kde si odporují, platí přísnější pravidlo.

---

## 0. Jedna věta, o co jde

> Člověk, který hledá dárek, má do minuty vybrat masáž, napsat vzkaz, vidět, jak bude poukaz
> vypadat a kolik zaplatí — a hned po odeslání mít v e-mailu číslo účtu, částku a QR platbu.

Stránka prodává. Žádný efekt nesmí zdržet vyplnění formuláře.

---

## 1. Proveditelnost — jde to?

**Ano.** Web běží na WEDOS webhostingu (Apache + PHP 8), takže formulář může posílat data
na malý PHP skript ve stejné doméně. Ten spočítá cenu, přidělí číslo objednávky a pošle dva e-maily.
Nepotřebujeme databázi, platební bránu ani žádnou službu třetí strany.

| Část | Řešení | Riziko |
|---|---|---|
| Formulář | statická `poukazy.html`, funguje i bez JS (klasický POST) | nízké |
| Výpočet ceny | **na serveru** z pevného ceníku, klientovi se nevěří | nízké |
| E-maily | PHP přes **SMTP schránku na uvolnito.cz** (PHPMailer, bez Composeru) | **střední: doručitelnost → SPF/DKIM na WEDOS** |
| Platba převodem | e-mail s číslem účtu, VS = číslo objednávky, QR Platba (SPAYD) | párování plateb je ručně |
| Vystavení poukazu | **fáze 1: Lukáš ho po připsání platby pošle/vytiskne ručně** | ruční práce |
| Spam | honeypot + minimální doba vyplnění + limit na IP, **bez reCAPTCHA** (cookies/GDPR) | nízké |

**Co blokuje ostré spuštění** (viz §9): e-mailová schránka na uvolnito.cz, číslo účtu,
poštovné, potvrzení délek a cen poukazů od klienta.

---

## 2. Soubory

```
poukazy.html                 podstránka s formulářem a živým náhledem poukazu
dekujeme-poukaz.html         potvrzení pro variantu bez JS (s JS se ukáže přímo na stránce)
api/poukaz.php               příjem formuláře, validace, cena, číslo objednávky, e-maily
api/lib/PHPMailer/…          3 soubory PHPMaileru (PHPMailer.php, SMTP.php, Exception.php)
api/lib/qrlib/…              generátor QR (phpqrcode, jeden soubor) — QR Platba do e-mailu
api/.htaccess                zakáže přímý přístup ke všemu kromě poukaz.php
../poukazy-data/             MIMO /www: config.php (SMTP heslo, účet), counter.txt, objednavky.csv, ratelimit/
```

- `config.php` s heslem **nikdy do gitu** (přidat do `.gitignore`) a **nikdy do /www**.
  Pokud WEDOS nedovolí zapisovat mimo `/www`, pak `/www/api/data/` s `Require all denied` v `.htaccess`.
- Ceník poukazů je v PHP **jedna konstanta** (zdroj pravdy). `poukazy.html` má ceny ve vlastním
  HTML kvůli bezJS variantě a SEO — při změně ceny se mění obojí; `poukaz.php` při nesouladu vždy vyhrává.
- Doplnit: odkaz „Poukazy" do hlavní i mobilní navigace, patičky, **sekci Dárkové poukazy na homepage**
  (je v povinném obsahu `SUPERPROMPT.md` §4 bod 8 a na webu zatím chybí), `sitemap.xml`,
  seznam nahrávaných souborů v `README.md`.

---

## 3. Formulář — pole a pravidla

Jeden formulář, čtyři logické kroky jako `<fieldset>` s `<legend>` pod sebou (ne wizard na více obrazovek —
na mobilu je scroll rychlejší než klikání „Další"). Vpravo (desktop) / nahoře u souhrnu (mobil) živý náhled.

### Krok 1 — Masáž (= pozadí poukazu)
Radio karty s fotkou, názvem a cenou. Vybraná masáž určuje **pozadí poukazu**.

| Masáž | Délky a cena (stav Reservio 6. 9. 2026) | Fotka pozadí (`assets/foto/opt/…`) |
|---|---|---|
| Relaxační masáž | 60 min 990 Kč · 90 min 1 290 Kč · 120 min 1 590 Kč | `2026-03-08-at-17-44-40` |
| Regenerační masáž | 60 min 990 Kč · 90 min 1 290 Kč · 120 min 1 590 Kč | `2026-03-08-at-18-06-27` |
| Lávové kameny | 60 min 1 190 Kč · 90 min 1 490 Kč | `2026-03-08-at-17-42-34` |
| Maderoterapie kombi | 60 min 1 190 Kč · 90 min 1 490 Kč | `2026-03-08-at-17-52-32` |
| Mangový rituál | 120 min 1 890 Kč | `2026-03-08-at-18-18-50` |
| Rychlé uvolnění | 30 min 750 Kč | `2026-03-08-at-18-06-56` |

Po výběru masáže se ukáže výběr **délky** (radio „pilulky" — pozor, `--radius: 4px`, žádné kulaté pilulky).
Má-li masáž jedinou délku, předvybere se a výběr se nezobrazí.
Ceny se **nevymýšlejí a neupravují** bez klienta (`README.md` → „Odkud jsou data").

### Krok 2 — Vzkaz pro obdarovaného
- `Pro koho` — nepovinné, max 40 znaků (na poukaze „Pro Janu").
- `Vzkaz` — nepovinný `<textarea>`, **max 160 znaků**, živé počítadlo, bez HTML, zalomení řádků max 3.
- `Od koho` — nepovinné, max 40 znaků.
- Pod polem jemná poznámka: „Vzkaz vytiskneme na poukaz přesně tak, jak ho napíšeš."

### Krok 3 — Doručení
Radio:
1. **E-mailem (PDF)** — zdarma. Poukaz jde **kupujícímu** (rozhodnutí klienta 3. 10. 2026), volba „poslat obdarovanému“ zatím není.
2. **Poštou (tištěný poukaz)** — `+ 99 Kč`. Zobrazí se adresa: jméno, ulice a č. p., město, PSČ
   (PSČ = 5 číslic, mezera dovolena). Poznámka: „Odesíláme do 3 pracovních dnů od připsání platby.“

Poplatek za poštu se okamžitě promítne do souhrnu i náhledu ceny.

**Datum doručení** *(nepovinné, `<input type="date">`)* `[čeká na odpověď klienta, §9 otázka 5]`
- E-mail: „Kdy poukaz doručit" — přesný den. Pošta: „Má dorazit nejpozději" — jen orientačně, pošta datum nezaručí.
- `min` = dnes + `[DOPLNIT, návrh 3]` dny u e-mailu, + `[DOPLNIT, návrh 7]` dnů u pošty (čas na převod + odeslání);
  `max` = dnes + 6 měsíců. Server hranice kontroluje znovu.
- Pod polem: „Když platba dorazí až po tomto datu, pošleme poukaz hned po jejím připsání."
- Nevyplněné = poukaz odejde hned po připsání platby.
- **Zvolené datum se vždy uvádí v potvrzovacím e-mailu zákazníkovi i v e-mailu Lukášovi** (§6),
  česky ve tvaru „24. 12. 2026 (čtvrtek)". Ukládá se i do `objednavky.csv`.
- Fáze 1: Lukáš pošle v daný den ručně. Fáze 2: plánovaná úloha (cron) na WEDOS pošle sama (§10).

### Krok 4 — Kontakt a platba
- Jméno a příjmení *(povinné)*, e-mail *(povinný)*, telefon *(nepovinný)*.
- **Platba: Bankovním převodem** — jediná volba, zobrazená jako předvybraný radio (připraveno na další metody),
  pod ní věta: „Číslo účtu a QR kód ti hned pošleme e-mailem. Poukaz odešleme po připsání platby."
- Checkbox *(povinný)*: „Souhlasím s [obchodními podmínkami](pravni-informace.html#op-poukazy)
  a beru na vědomí [zpracování osobních údajů](pravni-informace.html#…)."
- Souhrn: masáž · délka · doručení · poštovné · **celkem** (velké, `--font-display`).
- Tlačítko: **„Objednat poukaz za 1 290 Kč"** (částka se živě mění).

### Validace
- Nativní HTML (`required`, `type="email"`, `maxlength`, `pattern`) + vlastní hlášky česky.
- Chyby u pole (`aria-describedby`) + souhrn chyb nahoře s odkazy, `aria-live="polite"`.
- **Server validuje vše znovu** (whitelist masáží a délek, délky textů, e-mail `filter_var`, PSČ).

---

## 4. Živý náhled poukazu — „wow" prvek

Karta poměru **A6 na šířku (148 × 105, tj. ~1,41 : 1)** — stejný formát, jaký bude tisk/PDF.
- Pozadí = fotka vybrané masáže (`object-fit: cover`), přes ni overlay `--c-forest-dk` → text čitelný (≥ 4,5 : 1).
- Na kartě: logo/wordmark, „Dárkový poukaz", název masáže + délka, „Pro …", vzkaz (Fraunces italic),
  „Od …", platnost „6 měsíců od vystavení", číslo poukazu jako `UT-2026-XXXX` (v náhledu zástupné).
- Přepnutí masáže = prolnutí pozadí (`opacity`, 300 ms). Psaní vzkazu se propisuje okamžitě.
- Desktop: náhled `position: sticky` vedle formuláře. Mobil: náhled nahoře nad krokem 2, menší.
- Smí mít **jemné CSS 3D natočení** při pohybu myši (max ±6°), jen `pointer: fine`, vypnuté při
  `prefers-reduced-motion`. Žádný WebGL — tady nedává smysl.
- Bez JS: místo náhledu statický ukázkový poukaz (obrázek), formulář funguje dál.

**Formulář, ceny a souhrn se neanimují** (konverzní obsah, `CLAUDE.md` §3).

---

## 5. Server — `api/poukaz.php`

Pořadí kroků:
1. Jen `POST`, `Content-Type` form nebo JSON. Jinak 405.
2. **Antispam:** skryté pole `web` musí být prázdné; časové razítko z formuláře — odeslání dřív než
   za 4 s = tichý „úspěch" bez e-mailu; max **5 objednávek / IP / hodinu** (soubory v `ratelimit/`).
3. Validace (§3). Chyby vrací `422` + JSON `{errors: {pole: "hláška"}}`; bez JS přesměrování zpět s chybou.
4. **Cena se počítá jen tady:** `CENIK[masaz][delka] + (posta ? POSTOVNE : 0)`.
5. Číslo objednávky `UT-RRRR-NNNN` z `counter.txt` (`flock`, atomické). **Variabilní symbol** = `RRRRNNNN`.
6. Zápis řádku do `objednavky.csv` (datum, číslo, VS, masáž, délka, cena, doručení, jméno, e-mail, stav=`čeká na platbu`).
7. Odeslání **dvou e-mailů** (§6). Při selhání SMTP: zapsat do logu, uživateli vrátit chybu
   „Objednávku se nepodařilo odeslat — napiš prosím na … nebo zavolej …" (nikdy falešný úspěch).
8. Odpověď `200` + `{cislo, castka, vs}` → stránka ukáže potvrzení s platebními údaji a QR kódem.
   Bez JS: `303` redirect na `dekujeme-poukaz.html?c=…`.

Bezpečnost: všechny výstupy do HTML e-mailů `htmlspecialchars`, hlavičky e-mailu nikdy z uživatelského
vstupu (proti header injection) — `Reply-To` jen po `filter_var` validaci. Žádné `display_errors` v produkci.

**Vývojový režim:** `config.php` → `MAIL_MODE = 'log'` uloží e-maily do souboru místo odeslání.
Lokálně: `sudo apt install php-cli` a `php -S localhost:4000` (současný `python3 -m http.server` PHP nespustí).

---

## 6. E-maily

Odesílatel: `poukazy@uvolnito.cz` `[DOPLNIT — schránka zatím neexistuje]` přes SMTP WEDOS.
Před spuštěním ověřit SPF, zapnout DKIM ve WEDOS administraci a otestovat na mail-tester.com (cíl ≥ 9/10).
HTML + textová verze, tokeny z `CLAUDE.md`, inline styly, šířka 600 px, bez externích obrázků
(logo a QR jako CID přílohy).

### a) Zákazníkovi — „Objednávka poukazu UT-2026-0042"
1. Poděkování jednou větou, tónem webu (tykání, jako v obchodních podmínkách).
2. Rekapitulace: masáž, délka, pro koho, vzkaz, způsob doručení (+ adresa), **zvolené datum doručení**
   (nebo „hned po připsání platby"), cena.
3. **Platební údaje:** účet **1594284008/0100**, částka, VS, zpráva pro příjemce „Poukaz UT-2026-0042".
4. **QR Platba** (formát SPAYD: `SPD*1.0*ACC:CZ…*AM:1290.00*CC:CZK*X-VS:20260042*MSG:POUKAZ UT-2026-0042`).
5. Co bude dál: „Jakmile platba dorazí (obvykle do 1–2 pracovních dnů), pošlu ti poukaz e-mailem / odešlu poštou."
6. Platnost 6 měsíců od vystavení, poukaz se uplatňuje rezervací na Reservio, odkaz na podmínky,
   **poučení o odstoupení do 14 dnů** (§9 obchodních podmínek), kontakt, IČO.

### b) Lukášovi — „Nová objednávka poukazu UT-2026-0042 — 1 290 Kč"
Vše z formuláře v přehledné tabulce, `Reply-To` = zákazník, VS, částka a **datum doručení** nahoře
(datum zvýrazněné — podle něj Lukáš poukaz posílá), aby šla platba spárovat jedním pohledem do bankovnictví. Volitelně v příloze CSV řádek.

---

## 7. Stránka `poukazy.html` — obsah

1. **Hero** — **vědomá výjimka** ze `SUPERPROMPT.md` §1 (to platí pro homepage): fotka přes celou šířku
   s overlayem zůstává, ale výška jen `min-height: 60svh`, aby formulář byl blízko. `[schválit s klientem]` Claim např. „Daruj hodinu, kdy se nikdo nic nechce."
   `[návrh textu — schválit s klientem]`, jedno CTA „Vybrat poukaz" (kotva na formulář).
2. **Jak to funguje** — 3 kroky: vybereš a zaplatíš → poukaz přijde e-mailem/poštou → obdarovaný si zarezervuje termín.
3. **Formulář + náhled** (§3, §4).
4. **Krátké FAQ** — platnost, doplatek na dražší masáž, ztracený poukaz, lze koupit i ve studiu? — vše
   v souladu s `pravni-informace.html` §6, nic nového nevymýšlet.
5. Patička shodná s `index.html`.

Technicky: stejná hlavička, patička, fonty, `souhlas.css/js` jako `index.html`; CSS stránky inline
(stejně jako u indexu). GSAP jen na reveal sekcí 1–2, formulář bez animací. Strukturovaná data
`Product`/`Offer` pro poukazy. `<title>`: „Dárkové poukazy na masáž v Brně | uvolni.to".

---

## 8. Právní a GDPR úpravy

- `pravni-informace.html` — ochrana osobních údajů: nový účel **vyřízení objednávky poukazu**
  (jméno, e-mail, telefon, adresa doručení, údaje obdarovaného), právní základ plnění smlouvy,
  doba uložení (účetní doklady 10 let / ostatní `[DOPLNIT]`). Hosting WEDOS jako zpracovatel už je?
  ověřit v tabulce zpracovatelů.
- §6 Dárkové poukazy doplnit: způsob objednání online, platba převodem, poukaz se vystaví po připsání,
  poštovné, doba odeslání.
- §9 odstoupení už poukaz koupený na dálku pokrývá ✓.
- Doklad o zaplacení / faktura: `[OTÁZKA PRO KLIENTA — posílá k poukazu doklad? Je plátce DPH?]`.

---

## 9. Otázky pro klienta — bez nich se nestaví

**Stav: odesláno klientovi 2. 10. 2026, čeká se na odpověď. Se stavbou se začne až po odpovědích.**
Odpovědi zapsat sem pod každou otázku a podle nich upravit §3–§8.

Zpráva, jak odešla:

> Whoa, je to spíš do té druhé fáze, ale nějak to půjde. Jenom potřebuju pár věcí:
>
> 1. Číslo účtu a u jaké jsi bance
> 2. Poštovné budu potřebovat přesně, kolik to bude stát, jestli obyčejný nebo doporučený dopis a za jak dlouho to po zaplacení odešleš
> 3. E-mailová schránka, ze které se to bude posílat. Nepoužívej hlavní, rovnou na Wedosu založ poukazy@uvolnito.cz, budu k ní potřebovat přihlašovací údaje. A zapni tam u domény DKIM, ať to nepadá do spamu. Kdyžtak mi dej přístup do Wedosu a udělám to sám
> 4. Ceny masáží stejně jako na Reserviu? A chceš poukazy na všech 6 masáží, nebo jen na některé?
> 5. Poukaz se pošle až po zaplacení. Má jít kupujícímu, nebo rovnou tomu, kdo ho dostane? A můžu tam dát i datum doručení, třeba na Vánoce nebo k narozeninám? Zatím bys to v ten den poslal ty, později to půjde samo.
> 6. Chceš i poukaz na částku (třeba 1000 Kč na cokoliv), nebo jen na konkrétní masáž?
> 7. Platnost nechat 6 měsíců jako v podmínkách?
> 8. Posíláš k tomu doklad nebo fakturu? Jsi plátce DPH?
>
> Platba bude zatím jen převodem. Člověku po objednání hned přijde mail s číslem účtu a QR kódem, tobě přijde kopie objednávky a poukaz pošleš, až ti dojdou peníze.

Odpovědi (přišly 3. 10. 2026):
1. **Účet 1594284008/0100 — Komerční banka.** Kontrolní součet čísla účtu sedí (mod 11 = 0).
2. **Poštovné 99 Kč, odeslání do 3 pracovních dnů.** *Chybí:* obyčejný, nebo doporučený dopis.
3. Lukáš poslal přihlašovací údaje ke svému **osobnímu Gmailu** místo nové schránky `poukazy@uvolnito.cz`.
   **Heslo se nikam neukládá** (ne do repa, configu ani poznámek). Doporučeno: heslo změnit (bylo poslané
   zprávou), založit doménovou schránku na WEDOSu podle původního plánu; když zůstane Gmail, tak jen přes
   **heslo aplikace** (Google účet → Zabezpečení → dvoufázové ověření → Hesla aplikací), ne hlavní heslo —
   Gmail přes SMTP běžné heslo stejně nepřijme.
4. **Ceny jako v Reserviu** a mají jít později snadno změnit → ceník v jednom souboru (`api/cenik.php`
   nebo JSON), ze kterého čte formulář i server.
5. **Poukaz dostane kupující.** Lukáš by chtěl, aby odešel **hned** — viz řešení v §10 (odeslání jedním
   kliknutím po připsání platby). *Chybí:* datum doručení ano/ne.
6. *Bez odpovědi* — hodnotový poukaz. Zatím jen poukazy na masáž.
7. **Platnost 6 měsíců** — ano.
8. **Posílá doklad, není plátce DPH** → k poukazu automaticky doklad (PDF nebo v těle e-mailu) s textem
   „Nejsem plátce DPH", IČO 19696884, číslem dokladu, datem a částkou.

Ještě se doptat: druh dopisu (2), datum doručení (5), hodnotový poukaz (6), jestli poukazy na všech 6 masáží (4).

---

## 10. Fáze 2 (až fáze 1 poběží)

- **Odeslání k datu** — plánovaná úloha (cron) na WEDOS denně projde zaplacené objednávky a poukazy
  s dnešním datem doručení odešle sama.
- **Automatické PDF poukazu** (FPDF, jeden soubor, s fotkou masáže a fonty) → po označení platby se pošle samo.
- **Odeslání jedním kliknutím (nejbližší krok k „hned")** — v e-mailu Lukášovi bude tlačítko
  „Platba přišla — odeslat poukaz". Odkaz s jednorázovým tajným tokenem vygeneruje PDF poukaz a doklad
  a pošle je kupujícímu. Lukáš nemusí nic vyrábět ručně, jen v bance zkontroluje platbu a klikne.
  Poslat poukaz **před zaplacením** se nedoporučuje (nezaplacené poukazy by kolovaly).
- **Automatické párování plateb** — účet je u **Komerční banky** — její API je pro malého podnikatele složitější než u Fio; reálnější je e-mailové avízo o příchozí platbě nebo zůstat u odeslání jedním kliknutím
  (cron na WEDOS) → poukaz odejde bez zásahu Lukáše.
- Platba kartou (GoPay/Comgate) — až bude objem, který poplatky ospravedlní.
- Jednoduchý přehled objednávek pro Lukáše (heslem chráněná stránka nad CSV).

---

## 11. Autokontrola před odevzdáním

- [ ] Vypnutý JS → formulář jde odeslat, dorazí oba e-maily, zobrazí se děkovná stránka
- [ ] Podvržená cena / neexistující masáž v POST → server odmítne nebo spočítá správně
- [ ] Vyplněný honeypot / odeslání do 4 s → žádný e-mail
- [ ] Vzkaz s `<script>` a uvozovkami → v e-mailu i náhledu jako text
- [ ] Přepnutí na poštu → zobrazí se adresa, cena +poštovné v souhrnu, náhledu, tlačítku i e-mailu
- [ ] QR Platba z e-mailu načtená bankovní aplikací → správný účet, částka, VS
- [ ] mail-tester.com ≥ 9/10, e-mail nespadne do spamu v Gmailu a Seznamu
- [ ] `config.php` není v gitu ani dostupný přes URL; `api/data` vrací 403
- [ ] Mobil 375 px — žádný horizontální scroll, náhled čitelný, tlačítko nad klávesnicí
- [ ] Kontrast textu na náhledu ≥ 4,5 : 1 na všech 6 fotkách
- [ ] Barvy jen z tokenů (`grep -oE '#[0-9a-fA-F]{3,8}'`), terakota max 1× na obrazovku
- [ ] Žádné `[DOPLNIT]` v produkčním nasazení; `grep console.log` → 0
- [ ] Odkazy na poukazy: navigace, mobilní menu, homepage, patička, sitemap
