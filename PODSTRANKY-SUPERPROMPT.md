# SUPERPROMPT — podstránky masáží

Závazné zadání pro šest stránek jednotlivých masáží.
Nadřazené: `CLAUDE.md` (tokeny, typografie, technika), `SUPERPROMPT.md` (motion, autokontrola),
`README.md` → „Odkud jsou data" (ceny a recenze se nevymýšlejí). Kde si odporují, platí přísnější pravidlo.
Výjimky z `SUPERPROMPT.md`, které tu platí vědomě, jsou v §4 označené **[výjimka]**.

---

## 0. Jedna věta, o co jde

> Člověk, který do Googlu nebo Seznamu napíše „masáž lávovými kameny Brno", má přistát přímo na stránce
> té masáže, do deseti vteřin vědět, co ho čeká, kolik to stojí a kde to je — a mít po ruce Rezervovat.

Homepage prodává studio a Lukáše. **Podstránka prodává jednu konkrétní masáž** a chytá vyhledávání,
na které jednostránkový web nedosáhne.

---

## 1. Research — co z něj plyne (2. 10. 2026)

### Konkurence ve vyhledávání (Brno)
- **Maderoterapie Brno** — nejsilněji obsazené heslo. Výsledky drží specializovaná studia s vlastní
  stránkou pro maderoterapii (MaderoterapieBrno.cz, Maderotherapy Brno, Physio Suchánek, Beauty Institute,
  oneplus.cz s rozpadem *těla / obličeje*) a agregátory (Notino, Reservio, lymfodrenaz.cz).
  → Bez samostatné, obsahově bohaté stránky tu uvolni.to nemá šanci. Odlišení: **kombi** (masáž + madero)
  a „jeden terapeut", ne kosmetický salon.
- **Masáž lávovými kameny Brno** — hodně výsledků jsou dárkové e-shopy (Infinit dárky, Towell, Skrz),
  agregátory a thajské salony. Ceny v Brně cca **1 200–1 600 Kč / 60 min, 2 200–2 750 Kč / 90 min**.
  → uvolni.to (1 190 / 1 490 Kč) je **cenově pod průměrem** — to patří do textu i meta description.
  Silný dárkový záměr → odkaz na poukazy.
- **Regenerační masáž Brno** — rozdrobené: Reservio katalog, Slevomat, malé studia (KaMi, Royal Massage,
  fyzioterapie). Hledá se i „sportovní masáž Brno" — reference na webu sportovní masáž zmiňují
  („Využívám sportovní masáže po cvičení…"), takže je to legitimní vedlejší klíčové slovo regenerační stránky.
- **Relaxační masáž Brno** — nejobecnější a nejobsazenější, vyhrává lokální balíček Map. Podstránka tu
  pomáhá hlavně tím, že posílí relevanci celé domény a profilu na Mapách.
- Společný vzorec konkurence: obecný popis účinků, ceník, žádná osoba. **Nikdo nestaví na tom, kdo masíruje.**
  To je naše mezera.

### Co platí v SEO pro stránky služeb (2026)
- Každá stránka **60–70 % unikátního obsahu**. Šablonové stránky s vyměněným názvem masáže Google
  hodnotí jako tenký obsah. Sdílené bloky (průběh, kontakt, patička) jsou v pořádku, jádro musí být vlastní.
- **Strukturovaná data:** `Service` + `Offer` (ceny), `BreadcrumbList`, vazba na `LocalBusiness`
  z homepage přes `"provider": {"@id": "https://uvolnito.cz/#studio"}`.
- **FAQ rich results Google 7. 5. 2026 zrušil.** Schéma `FAQPage` ale zůstává platné, nic nerozbíjí
  a dál ho čtou Bing a AI vyhledávače (Perplexity, ChatGPT search). → FAQ dělat kvůli lidem, schéma přidat,
  rich snippet nečekat. **Žádné vymyšlené otázky jen kvůli schématu.**
- Lokální signály: adresa a čtvrť v textu (Skřivanova, u Lidické, kousek od Lužánek), mapa / odkaz na Mapy,
  NAP (název–adresa–telefon) shodné s Google profilem a Firmy.cz.
- **Seznam** má v ČR pořád významný podíl: čte stejné meta tagy a strukturovaná data, lokálně ale
  vychází z **Firmy.cz** a Mapy.com → mimo web zkontrolovat a vyplnit profil na Firmy.cz se stejnými službami.

Zdroje: výsledky vyhledávání Google k heslům výše; přehledy změn FAQ schématu 2026
(quattr.com, seostrategy.co.uk, getpassionfruit.com); local SEO přehledy 2026 (bippermedia.com,
digitalneighbor.com, opace.agency).

---

## 2. Mapa stránek a klíčových slov

| Stránka (URL) | Hlavní heslo | Vedlejší hesla | Priorita |
|---|---|---|---|
| `/masaz-lavovymi-kameny` | masáž lávovými kameny Brno | hot stone, teplé kameny, dárek | **1** — vzorová stránka |
| `/maderoterapie` | maderoterapie Brno | maderoterapie těla, lymfa, otoky nohou, celulitida | **1** |
| `/regeneracni-masaz` | regenerační masáž Brno | sportovní masáž Brno, masáž zad, ztuhlá šíje | **1** |
| `/relaxacni-masaz` | relaxační masáž Brno | masáž celého těla, olejová masáž, stres | 2 |
| `/mangovy-ritual` | mangový rituál Brno | masáž s peelingem, rituál, dárek pro ženu | 3 |
| `/rychle-uvolneni` | masáž šíje Brno | 30minutová masáž, masáž v poledne | 3 `[OTÁZKA — co přesně se dělá?]` |

- URL **bez diakritiky, bez `.html`**, bez „brno" v adrese (Brno patří do title a H1, adresa má zůstat krátká).
  Soubory budou `masaz-lavovymi-kameny.html` atd., `.htaccess` je servíruje bez přípony a `.html` verzi
  přesměruje 301. Staré `masaz-relaxacni.html` atd. (v gitu, nikdy veřejně neindexované) se nepoužijí.
- Priorita 3 = stránka vznikne, ale dokud nebude mít dost vlastního obsahu, dostane `noindex`
  (stejně jako se kdysi odpojily staré podstránky). Tenká stránka uškodí víc, než chybějící.

### Šablona title / description / H1
```
<title>   {Masáž} Brno — {délky} od {cena} Kč | uvolni.to          (max ~60 znaků)
<meta description>  {čím je masáž jiná, 1 věta}. {délky a cena}. Studio u Lidické, platba i Edenred a Benefit plus.  (140–155 znaků)
<h1>      {Masáž} v Brně   (nebo přirozenější tvar: „Masáž lávovými kameny v Brně")
```

---

## 3. Anatomie podstránky (pořadí sekcí)

1. **Hlavička** — stejná jako homepage, ale odkazy míří na `/#sluzby` atd., logo vede na `/`.
   Mezi odkazy navíc „Masáže ▾" s rozbalovacím seznamem všech šesti (desktop) / v mobilním panelu.
2. **Drobečková navigace** — `Domů / Masáže / Lávové kameny` (viditelná, malá, nad H1).
3. **Hero** — H1, jedna věta „pro koho a proč", **fakta na jeden pohled** (délky, cena, intenzita tlaku),
   dvě akce: Rezervovat (primární) + Darovat poukaz (textová). Viz §4.
4. **Pro koho je / kdy má smysl** — 3–5 konkrétních situací („Když jsou záda ztuhlá tak, že na ně ruce
   nestačí…"). Tady je unikátní obsah stránky číslo jedna.
5. **Jak tahle masáž probíhá** — kroky **specifické pro tuhle masáž** (u kamenů: nahřátí, kameny na zádech,
   masáž kameny, závěr). Ne kopie čtyř obecných kroků z homepage.
6. **Ceník** — délky × ceny, co je v ceně, benefity. **Neanimuje se.**
7. **Kdy raději ne** — kontraindikace stručně a lidsky + odkaz na obchodní podmínky §7. Buduje důvěru,
   konkurence to skoro nemá.
8. **Lukáš** — malý blok s portrétem: kdo masíruje, jedna věta k této masáži, certifikace `[DOPLNIT]`.
   (E-E-A-T: autorita a zkušenost.)
9. **Reference** — 1–2 doslovné recenze se zdrojem. Ideálně takové, které tuhle masáž zmiňují;
   když žádná není, obecná — **nikdy upravená**.
10. **FAQ** — 4–6 otázek, které lidé u té masáže opravdu řeší.
11. **Další masáže** — tři karty zbývajících masáží (vnitřní prolinkování), oblouky jako na homepage.
12. **Rezervace + kontakt** — CTA blok, adresa s čtvrtí, odkaz na Mapy (mapa jen přes souhlas jako na homepage).
13. **Patička** — shodná s homepage, seznam masáží v ní vede na podstránky.
14. **Mobilní lišta** — Zavolat / Rezervovat, jako na homepage.

Rozsah textu: **600–1 000 slov** vlastního obsahu na stránku (bez hlavičky, patičky a sdílených bloků).

---

## 4. Design — „stejný, ale ne totožný"

### Co zůstává **stejné** (aby to byl jeden web)
- Tokeny, fonty (self-hosted), tlačítka, hlavička „ledger bar", patička, mobilní lišta, cookie lišta.
- **Oblouk** (`--arch`) jako leitmotiv: fotky v obloucích, obloukové předěly, obloučková značka v FAQ.
- Vlasové linky s popiskem (`.rule`), `.label` s pískovou čárkou, číslování „03 — Teplo".
- Tón textu: tykání, první osoba (Lukáš), krátké věty, česká typografie.

### Co je **jiné** (aby podstránka měla vlastní charakter)
| Homepage | Podstránka |
|---|---|
| Celoobrazovkové tmavé hero, fotka přes celou plochu | **[výjimka]** nižší hero (`min-height: 72svh`), H1 + fakta musí být vidět bez scrollu |
| Scroll storytelling, dvě pinované sekce | **Žádný pin.** Klidné čtení, odkrytí jen `once: true` |
| Prodává studio a osobu | Prodává **jednu masáž** — její číslo a slovo („03 — Teplo") je velký typografický prvek |
| Střídání tmavých a světlých sekcí | Převážně **světlá** (krémová / písková), tmavá jen hero nebo jen jeden pruh |
| Karty šesti masáží | **Fakta na jeden pohled** (délka · cena · tlak · pro koho) — nový prvek, jen na podstránkách |

- Každá masáž má **vlastní slovo a číslo** z homepage (01 Klid, 02 Úleva, 03 Teplo, 04 Lymfa, 05 Rituál,
  06 Rychle) — to je jediná „barva" navíc. **Nové barvy se nepřidávají**, ani pro rozlišení masáží.
- **Intenzita tlaku** jako škála 1–5 (pět oblouků, vyplněné = síla) — vizuální podpis podstránek.
  Hodnoty `[OVĚŘIT u Lukáše]`, dokud je nepotvrdí, se v návrzích píšou jako odhad.
- Motion: reveal sekcí (`once: true`), jemný parallax fotky v hero (max 8 %), nic dalšího. Ceník, kontakt
  a fakta v hero se neanimují.

---

## 5. Obsah — pravidla

- **Ceny, délky a recenze jen z existujících zdrojů** (Reservio, homepage). Recenze doslovně se jménem,
  zdrojem a datem.
- Obecné informace o technice (co jsou lávové kameny, jak funguje maderoterapie) psát smí — **konkrétní
  tvrzení o Lukášově praxi ne** (teplota kamenů, počet kamenů, oleje, kurzy). Ta se značí `[OVĚŘIT]`.
- **Žádné zdravotní sliby.** Masáže nejsou zdravotní služba (`pravni-informace.html` §7). Ne „léčí",
  „odstraní celulitidu", „detoxikuje" — ano „uleví", „prohřeje", „podpoří", „může pomoct".
- Kontraindikace v souladu s `pravni-informace.html` §7 a FAQ na homepage, nic navíc nevymýšlet.

### Otázky pro Lukáše (na každou masáž)
1. Pro koho ji nejčastěji doporučuješ? S čím za tebou lidi chodí?
2. Jak probíhá krok za krokem? (u kamenů: jak je nahříváš, kam je dáváš, jak dlouho)
3. Jaký tlak (1 = jemně, 5 = hodně silně)?
4. Kdy ji nedělat / kdy se předem ozvat?
5. Co člověk cítí po ní a druhý den?
6. Na co se lidi u ní nejčastěji ptají?
7. Jaký kurz / certifikát k ní máš?
8. U Rychlého uvolnění: co přesně se za 30 minut dělá (šíje, záda, hlava)?

---

### Stav ověření (2. 10. 2026)
- **Vybraná podoba:** hero z varianty B (fotka přes celou šířku + fakta v pruhu), zbytek z varianty A.
  Vzor: `navrhy-podstranky/masaz-lavovymi-kameny.html`. Citát na středu, dole karty všech pěti zbývajících masáží.
- **Ověřeno v Reserviu:** všechny ceny a délky, popisy služeb (Lávové kameny = „Klidnější tempo. Když cítíš stres
  a potřebuješ prohřát svaly."; Maderoterapie 90 min včetně madero pleti; plný popis Mangového rituálu),
  telefon, adresa, hodnocení 5,0 z **32** recenzí, recenze Karin 26. 8. 2026 doslovně.
- **Nesouhlasí — vyřešit s Lukášem:** otevírací doba. Web: Po–St 12–22, Čt 9–19, Pá 9–18.
  Reservio: Po–Pá 8–22, So–Ne 9–20. Týká se i homepage a JSON-LD. Na webu je i zastaralý počet recenzí na Reserviu (24).
- Na přání klienta jsou **neověřitelné texty nahrazené lorem ipsum** (výjimka z `CLAUDE.md` §2, jen pro návrhy).
- Škála tlaku zatím **není nasazená** — „Tempo: klidnější" je ověřené z Reservia, číslo tlaku čeká na Lukáše.

- **Všech šest stránek** je v `navrhy-podstranky/` a generuje je `_generuj.py` ze `_sablona.html`
  (data a zdroj každého textu jsou v hlavičce skriptu). Recenze: každá stránka jiná, doslovně z Reservia.
  OG obrázky 1200×630 v `navrhy-podstranky/og/` (při nasazení do `assets/og/`).

## 6. Technika

- Soubory `masaz-lavovymi-kameny.html` … v kořeni webu (relativní cesty k `assets/` zůstanou stejné).
- **Společné CSS** (tokeny, fonty, hlavička, patička, tlačítka, FAQ, mobilní lišta) vytáhnout do
  `assets/css/zaklad.css` a podstránky ho sdílí; vlastní CSS podstránek v `assets/css/podstranka.css`.
  Homepage zatím zůstává s inline CSS — přepojit ji na `zaklad.css` je samostatný úkol (riziko regrese).
- JS: GSAP + ScrollTrigger + Lenis jen pokud stránka opravdu animuje; menu, hlavička a FAQ se sdílí
  v `assets/js/podstranka.js`.
- Obrázky: `<picture>` s WebP, `width/height`, hero `fetchpriority="high"` + `preload`, zbytek `lazy`.
  Pro každou stránku vlastní **og:image 1200×630** (výřez fotky té masáže).
- `canonical` na URL bez přípony, `og:*`, `twitter:card`, `lang="cs"`.
- **Homepage se upraví:** karty masáží vedou na podstránky (dnes vedou rovnou na Reservio — Rezervovat
  zůstane jako samostatné tlačítko), patička odkazuje na podstránky, `sitemap.xml` + `lastmod`.
- Rezervace: `[OVĚŘIT]`, jestli Reservio umí odkaz rovnou na konkrétní službu — pak tlačítko vede tam.

---

## 7. Autokontrola

- [ ] Title ≤ 60 znaků, description 140–155, jediné H1 s heslem a „Brno"
- [ ] `canonical`, `og:image` (absolutní URL), Service + Offer + BreadcrumbList validní (validator.schema.org)
- [ ] `provider` míří na `https://uvolnito.cz/#studio`
- [ ] ≥ 600 slov vlastního obsahu, ≤ 40 % shodného textu s ostatními podstránkami
- [ ] Ceny shodné s homepage a Reserviem; recenze doslovné se zdrojem
- [ ] Žádné zdravotní sliby (`grep -iE 'léčí|detox|odstraní'` → 0)
- [ ] H1, fakta a Rezervovat vidět bez scrollu na 375 × 667
- [ ] Žádná pinovaná sekce; ceník a kontakt bez animace; `prefers-reduced-motion` vypne parallax
- [ ] Bez JS je stránka kompletní (menu přes `<details>`, FAQ přes `<details>`)
- [ ] Barvy jen z tokenů, terakota max 1× na obrazovku
- [ ] Odkazy: homepage karta → podstránka, patička, „Další masáže", sitemap
- [ ] LCP < 2,5 s, CLS < 0,1 (Lighthouse mobil)
- [ ] `[DOPLNIT]` / `[OVĚŘIT]` → před ostrým nasazením 0 výskytů
