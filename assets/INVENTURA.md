# Inventura obrazových podkladů

Staženo 24. 8. 2026 z `uvolnitomasaze.cz` (WordPress 7.1) — přes REST API `/wp-json/wp/v2/media` + parsování HTML homepage a podstránek. **42 souborů**, vždy v největší dostupné variantě (WP thumbnaily odfiltrovány).

---

## `logo/` — 7 souborů

| Soubor | Rozměr | Poznámka |
|---|---|---|
| `Loga-02-removebg-preview.png` | **283 × 142** | **Nejlepší dostupná varianta.** RGBA, průhledné pozadí. Wordmark „uvolni.to" + „MASÁŽE", uvnitř „o" je stylizovaný plamínek/list. |
| `cropped-Loga-02-removebg-preview.png` | 282 × 95 | Ořez předchozího, průhledné |
| `Loga-03.jpg` | 283 × 142 | Stejný motiv, JPG bez průhlednosti |
| `Loga-01-e1772741257480.jpg` | — | Varianta loga |
| `cropped-Loga-01-…jpg` | — | Ořez |
| `logo.png` | 156 × 40 | Nejmenší, colormap |
| `default.svg` | vektor | **Není logo** — placeholder z WP šablony (1 KB) |

> ⚠ **Logo je jen rastr, maximálně 283 px široký.** Pro nový web nedostatečné — na retina displeji a v hlavičce se rozpadne. Viz „Problémy" níže.

---

## `foto/` — 20 souborů (vlastní fotky studia)

**Všechny na výšku.** Rozměry 900×1600, 1066×1600, případně 1354×2048 a 1580×2048.

### Tier A — profesionální fotoprodukce (použitelné)
Teplé světlo, měkké bokeh, čistá kompozice:

| Soubor | Rozměr | Obsah |
|---|---|---|
| `…18.12.14.jpeg` | 1580 × 2048 | **Portrét Lukáše** — bílé polo, drží lávové kameny, úsměv. Nejlepší snímek ze setu. Klíčový pro sekci „O mně". |
| `…17.42.34.jpeg` | 1066 × 1600 | Detail lávových kamenů na zádech, krásné bokeh. Ideál pro sekci služeb. |
| `…17.44.40.jpeg` | 1066 × 1600 | Ze stejné série |
| `…17.55.14`, `…17.56.21`, `…18.06.27`, `…18.06.56` | 1066 × 1600 | Ze stejné série — proces masáže |
| `…20.59.51.jpeg` | 1066 × 1600 | Ze stejné série |

### Tier B — telefonní snímky (jen jako doplněk)
| Soubor | Rozměr | Obsah |
|---|---|---|
| `…17.52.32.jpeg` | 1354 × 2048 | Maderoterapie — dřevěný váleček na noze. Obsahově cenné (jediný důkaz maderoterapie), kvalitou slabší. |
| `…17.39.13*.jpeg` (4 ks) | 900 × 1600 | **Interiér studia** — masážní lehátko, zlatá štuková stěna, dřevěná komoda, sukulenty. Tmavé, šum, v záběru digitální budík a kabely. |
| `…22.51.55*.jpeg` (3 ks) | 900 × 1600 | Interiér, podobná kvalita |
| `…18.18.50`, `…18.19.20` | 900 × 1600 | Doplňkové |
| `…14.15.45…jpeg` | 1147 × 1650 | Doplňkové |
| `Snimek-obrazovky-…jpg` | 331 × 499 | **Screenshot, ne fotka.** Nepoužívat. |

---

## `ostatni/` — 4 soubory

| Soubor | Rozměr | Poznámka |
|---|---|---|
| `EdenredBenefits-samolepka.jpg` | 1426 × 1658 | Samolepka Edenred — podklad pro sekci benefitů |
| `Kopie-navrhu-…Price-List-…png` | 346 × 146 | **Navzdory názvu to NENÍ ceník — je to barevná verze loga.** Zdaleka nejcennější soubor z celé sady, viz níže. |
| `Univerzalni_banner_na_web_200x200_px.png` | 200 × 200 | Malý banner |
| `…-150x150.png` | 150 × 150 | Thumbnail téhož |

---

## `_sablona-nepouzivat/` — 11 souborů

Demo obrázky z WordPress šablony (`footer-cta.jpg`, `our-experience.jpg`, `ours-journey.jpg`, `testimonial-skip-01…03`, `post-images-01…03`, `author-skip-img.png`, `mt-sample-background.jpg`).

**Nejsou to fotky klienta** — jsou to cizí stock snímky dodané s šablonou. Do nového webu nepatří (a hrozí u nich licenční problém). Staženo jen pro úplnost; složku lze smazat.

Mimochodem: `footer-cta.jpg` má **1,4 MB** a `post-images-01` **1,2 MB** — nezoptimalizované stocky jsou hlavní důvod, proč je současný web pomalý.

---

---

## 🎨 Klíčový nález: barvy značky

Soubor `ostatni/Kopie-navrhu-…Price-List-…png` (346 × 146) je **barevná verze loga** — na webu se nikde nepoužívá, všude visí jen černobílá varianta. Barvy jsem změřil přímo z pixelů:

| Barva | Hex | RGB | Podíl plochy | Kde je v logu |
|---|---|---|---|---|
| **Zelená** | `#304F47` | 48, 79, 71 | 5,8 % | text „uvolni.to" a „MASÁŽE" |
| **Písková** | `#CBBBA0` | 203, 187, 160 | 0,6 % | plamínek/list uvnitř písmene „o" |

Zbytek plochy je bílá.

**Proč je to důležité:** designový systém v `CLAUDE.md` byl původně postavený na zelené `#425E40` odkoukané z Antara Spa. Skutečná značková zelená `#304F47` je **chladnější, do teala** — a písková `#CBBBA0` je přirozený teplý protipól. Paleta je tím pádem odvozená z klientovy vlastní identity, ne z cizí předlohy. `CLAUDE.md` je aktualizovaný.

**Doporučení:** používat barevné logo, ne černobílé. Černobílá varianta zahazuje polovinu identity.

---

## Problémy, které je nutné vyřešit

### 1. Logo — vektor dodán, ale s živým textem — STÁLE BLOKUJE
*(aktualizováno 3. 9. 2026)* Klient dodal `logo/Uvolnito_LOGO.svg` a `Uvolnito_LOGO_CB.svg`. Vektor tedy existuje, ale
**wordmark v něm není v křivkách** — je to živý `<text>` ve fontu `MADE Evolve Sans`. Bez nainstalovaného fontu ho
prohlížeč vysází náhradním písmem: ověřeno v Chrome, „MASÁŽE“ se rozpadne na „MAS ÁŽE“ a obsah přeteče viewBox
(bbox 467 px na canvasu 283 px). Použitelný je zatím jen `Uvolnito_ikonavagina.svg` — ten je čistá cesta bez textu.

**Řešení:** vyžádat export SVG s **textem převedeným do křivek** (Illustrator: Text → Vytvořit obrysy).
Do té doby varianty používají rastry odvozené z barevné verze loga:
`logo/ODVOZENO-wordmark-barevne.png` (odstraněné bílé pozadí) a `logo/ODVOZENO-wordmark-kremove.png` (pro tmavá pozadí).

### 2. Neexistuje jediná fotka na šířku — OMEZUJE DESIGN
Všech 20 fotek je na výšku (poměr ~9:16). Předlohy, které sis vybral — **Antara (7★) i Saltwater (6★)** — stojí na širokoúhlých hero záběrech přes celou obrazovku.

Možnosti:
- **a)** Hero postavit na **split layout** (text vlevo, portrét na výšku vpravo) — funguje s tím, co máme, a sedí na sólo maséra.
- **b)** Ořezat `…18.12.14.jpeg` (1580×2048) na šířku — vejde se max ~1580×890, na full-width hero na velkém monitoru je to málo.
- **c)** Dofotit 5–8 širokoúhlých záběrů.

Doporučuju **a) teď, c) před ostrým spuštěním.**

### 3. Interiérové fotky nejsou v kvalitě zbytku
Snímky studia jsou tmavé telefonní fotky s budíkem a kabely v záběru. Vedle profesionálního portrétu Lukáše to bude vidět.
**Řešení:** nepoužívat je velké — jen jako malé dlaždice v galerii, nebo dofotit.

### 4. Chybí fotky sportovní masáže
Máme lávové kameny (relaxační) a maderoterapii. **Sportovní masáž — jedna ze tří hlavních služeb — nemá jediný vlastní snímek.**

---

## Co z toho plyne pro fotoprodukci

Až se bude fotit, minimální seznam:
- 5–8 záběrů **na šířku** pro hero a sekční předěly
- sportovní masáž v akci (chybí úplně)
- interiér studia při denním světle, uklizený
- 2–3 portréty Lukáše (máme dobrý, ale jeden je málo)
- detaily: ruce, olej, ručníky — dobře se z nich dělají textury a předěly
