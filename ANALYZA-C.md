# Varianta C „Antara“ — kritická a SEO analýza

**Předmět:** `varianta-c-antara.html` (39,6 kB) · **Datum:** 5. 9. 2026
**Důvod:** klient vybral C jako výchozí směr. Než se na ní začne stavět ostrý web,
je potřeba pojmenovat, co je v ní dobře, co je rozbité a co ji drží mimo Google.

**Shrnutí jednou větou:** vizuálně je C nejsilnější z celé sady a designový směr
je správný — ale jako *web* je to zatím jednostránkový katalog bez adresy, bez ceníku
a s H1 utopeným v logu. **Pro lokální SEO je v současné podobě prakticky neviditelná.**

---

## 1. Co je na C dobře (a musí zůstat)

| Prvek | Proč funguje |
|---|---|
| **Oblouk jako leitmotiv** (`--arch`, `.arch-div` SVG předěly) | Jediný skutečně vlastní tvarový nápad v celé sadě. Drží stránku pohromadě a odlišuje ji od každé šablony. |
| Hluboká zelená + krémová, přesné tokeny | Odvozeno z loga klienta, ne z cizí předlohy. Kontrast sedí. |
| „Účetní rám“ — vlasové linky s příčkami (`.rule`) | Levné, elegantní, dává stránce rytmus bez obrázků. |
| Rozstřelené jméno v hero s odkrytím po písmenech | Nejsilnější animace v projektu. **Nápad je dobrý, jen je na špatném místě** — viz §3. |
| Parallax max 12 %, `scrub`, `once:true` | Střídmé, v souladu s `CLAUDE.md` §3. |
| Pojistka `scrollEnd` proti neviditelnému obsahu | Dobrý obranný reflex, chybí ostatním variantám. |

---

## 2. SEO analýza

Hlavní akviziční kanál masážního studia je **lokální vyhledávání** — dotazy typu
`masáže [město]`, `sportovní masáž [město]`, `maderoterapie [město] cena`.
Podle toho je celá analýza vážená.

### 2.1 Kritické — blokuje výsledky

**① H1 je promrhaná na logo.**
```html
<h1 class="hero-word" id="heroWord" aria-label="uvolni.to">
  <span class="ch">u</span><span class="ch">v</span>… <!-- 9× písmeno -->
</h1>
```
H1 je nejsilnější on-page signál na stránce a je v ní **jen značka**. Navíc jsou
písmena rozsekaná do `<span>` s `aria-hidden="true"` — pro čtečku i pro parser zbývá
jen `aria-label`. Google z toho vyčte „uvolni.to“ a nic víc.

> **Náprava:** H1 = služba + místo, ne značka.
> `Masáže [MĚSTO] — relaxační, sportovní a brazilská maderoterapie`
> Grafické rozstřelení jména může zůstat, ale jako `<p>` nebo `<div>`, ne jako H1.

**② Title a description neobsahují ani službu, ani město.**
```
<title>uvolni.to — masáže | Lukáš Václavek</title>
```
Značka, kterou nikdo nehledá, je na prvním místě. Chybí lokalita — tj. to jediné,
podle čeho se tenhle typ služby hledá.

> **Náprava:** `Masáže [MĚSTO] — relaxační, sportovní, maderoterapie | uvolni.to`
> (≈ 60 znaků, klíčové slovo vlevo, značka až za oddělovačem)

**③ Adresa se na stránce nevyskytuje ani jednou.**
V JSON-LD je `"streetAddress":"DOPLNIT"`. Bez NAP (Name-Address-Phone) konzistentního
s Google Business Profilem **studio nemůže vstoupit do lokálního balíčku map.**
To je jednotlivě největší ztráta v celém projektu — větší než cokoli vizuálního.

**④ Telefon je nefunkční:** `href="tel:+420"` — samotná předvolba. Na mobilu proklik nic nevytočí.

**⑤ Favicon míří na neexistující soubor:** `assets/logo/piktogram.svg` → 404.
Ve složce jsou `Uvolnito_ikonavagina.svg`, `Uvolnito_LOGO.svg` a odvozené PNG. Viz §3.

**⑥ Jedna stránka nemůže rankovat na tři různé služby.**
Relaxační masáž, sportovní masáž a maderoterapie mají různý záměr hledajícího,
různou konkurenci i různou cenu. Na jedné URL se perou o stejný title a H1.

> **Náprava:** samostatná podstránka pro každou službu —
> `/masaze/relaxacni-masaz/`, `/masaze/sportovni-masaz/`, `/masaze/brazilska-maderoterapie/`
> Každá s vlastním title, H1, popisem, cenou, FAQ a `Service` schématem.
> *(Přesně to, co si klient nezávisle vyžádal jako proklik z karty — potřeba se kryje.)*

### 2.2 Vážné

**⑦ Chybí `<link rel="canonical">`, Open Graph i Twitter Card.**
Sdílení na Facebooku a WhatsAppu — pro tuhle klientelu hlavní kanál doporučení —
vygeneruje odkaz bez obrázku a bez popisku.

**⑧ Strukturovaná data jsou kostra, ne signál.**
Aktuálně jen `LocalBusiness` s `DOPLNIT` a `Offer` bez ceny.

> Chybí: `@type` zpřesnit na `["HealthAndBeautyBusiness","LocalBusiness"]`,
> dále `url`, `image`, `telephone`, `geo`, `openingHoursSpecification`,
> `areaServed`, `sameAs` (Google profil, Facebook, Instagram, Reservio),
> a u každé `Offer` skutečná `price` + `priceCurrency: "CZK"`.
> `aggregateRating` **jen pokud jsou recenze reálné a ověřitelné** — jinak je to
> porušení pravidel a riziko manuální penalizace.

**⑨ Ceník na stránce chybí.**
Ceny jsou jen jako `[DOPLNIT] Kč` na kartách, samostatný ceník neexistuje.
`cena masáže [město]` je přitom dotaz s vysokým konverzním záměrem.

**⑩ Žádný `sitemap.xml`, žádný `robots.txt`.**
Nutné, jakmile vznikne víc než jedna URL.

**⑪ Preloader poškozuje Core Web Vitals.**
```css
.preload{position:fixed; inset:0; z-index:200; background:var(--c-forest)}
```
Neprůhledná vrstva přes celý viewport, která odchází nejdřív po **~2,25 s**
(1,1 s počítadlo + 0,15 s prodleva + 1 s odjezd). Do té doby uživatel nevidí nic.

Navíc **to počítadlo lže** — `gsap.to(n, {v:100, duration:1.1})` je časová animace,
ne skutečný postup načítání. Ukazuje 100 % bez ohledu na to, jestli je hero fotka stažená.

> Souhlasím s klientem, že preloader pryč. **Ale animace odkrytí jména je dobrá —
> patří rovnou do hero, kde nic neblokuje.** Viz §3.

### 2.3 Drobnosti s reálným dopadem

- **Názvy obrázků nesou nulový signál:** `WhatsApp-Image-2026-03-08-at-17.55.14.jpeg`.
  Přejmenovat na `relaxacni-masaz-lavove-kameny-uvolnito.jpg` apod. — pomáhá i obrázkovému vyhledávání.
- **Formát:** JPEG. AVIF/WebP ušetří 40–60 % při stejné kvalitě (`CLAUDE.md` §4 to už vyžaduje).
- **Hero fotka je na výšku** (1066 × 1600) roztažená přes celou obrazovku — na širokém monitoru se stahuje víc pixelů, než se použije.
- **`overflow-x:hidden` na `body`** dělá ze stránky scroll kontejner a rozbíjí `position:sticky`. Použít `overflow-x:clip`.
- **Chybí `<html lang>` varianty pro SK** — recenze jsou dnes česko-slovensky míchané (viz `PROJEKT.md` §1). Sjednotit na CZ.
- **`href="#"`** — jeden mrtvý odkaz.
- Chybí FAQ blok. `FAQPage` schéma je dnes hlavní vstup do AI odpovědí a rozšířených výsledků.

### 2.4 Priorita nápravy

| # | Zásah | Dopad | Pracnost | Blokuje |
|---|---|---|---|---|
| 1 | Doplnit adresu + telefon, NAP shodné s Google profilem | ★★★★★ | triviální | **klient** |
| 2 | Přepsat H1, title, description na službu + město | ★★★★★ | malá | město |
| 3 | Podstránky tří služeb | ★★★★☆ | střední | ceny |
| 4 | Ceník s cenami a délkami | ★★★★☆ | malá | **klient** |
| 5 | Dotáhnout JSON-LD (LocalBusiness + Service + FAQ) | ★★★☆☆ | malá | adresa, ceny |
| 6 | Zrušit preloader | ★★★☆☆ | triviální | — |
| 7 | Canonical + Open Graph | ★★★☆☆ | triviální | doména |
| 8 | Přejmenovat a překonvertovat obrázky | ★★☆☆☆ | střední | — |
| 9 | sitemap.xml + robots.txt | ★★☆☆☆ | triviální | doména |

**Body 1 a 4 nezvládnu bez klienta.** Zbytek jde udělat hned a je v nových variantách udělaný.

---

## 3. Kritika mimo SEO

### 3.1 Loga na stránce nejsou

Hlavička i patička sázejí wordmark **jako text**:
```html
<a href="#hero" class="hd-mark">uvolni<span class="dot">.</span>to</a>
```
Je to Fraunces, ne firemní písmo. Značka tedy na vlastním webu nikde nevystupuje
ve své skutečné podobě — a favicon navíc míří do prázdna.

**Co je k dispozici** (`assets/logo/`, podrobně v `INVENTURA.md`):

| Soubor | Použitelnost |
|---|---|
| `ODVOZENO-wordmark-barevne.png` (336×124, RGBA) | ✅ na světlé pozadí |
| `ODVOZENO-wordmark-kremove.png` (336×124, RGBA) | ✅ na tmavé pozadí |
| `Uvolnito_ikonavagina.svg` (1 kB) | ✅ favicon — čistá cesta, bez textu |
| `Uvolnito_LOGO.svg` / `_CB.svg` | ❌ **wordmark je živý `<text>`** ve fontu MADE Evolve Sans. Bez fontu se rozpadne na „MAS ÁŽE“ a přeteče viewBox. |

> **Stále blokuje:** vyžádat od klienta export SVG **s textem v křivkách**
> (Illustrator: Text → Vytvořit obrysy). Do té doby jsou 336px PNG strop —
> na retina hlavičce vyjdou tak na 112 px šířky, což zrovna stačí.

### 3.2 Karty služeb jsou slepá ulička

Karta ukáže fotku, název, dvě věty a cenu — a **nikam nevede**. Kdo chce vědět víc,
nemá kam kliknout. Zároveň je to jediné místo na stránce, kde se dá čekat interakce,
a nic se tam neděje.

### 3.3 Chybí vrchol

Stránka má rovnoměrné tempo od začátku do konce: odkrývání, parallax, odkrývání.
Není v ní **jediný moment, kde by se zastavil dech.** Oblouk se přitom sám nabízí —
je to hotový tvar, který může fungovat jako průchod.

### 3.4 Další drobnosti

- Nav odkazuje na `#proces`, ale sekce „Průběh“ nemá vlastní nadpis v osnově — jen `id` na obalu.
- V osnově jsou dvě `<h4>` bez nadřazené `<h3>` — nekonzistentní hierarchie.
- „Rezervace“ je nadpis, ale rezervační kalendář ve stránce chybí (odchod na Reservio).

---

## 4. Co z toho plyne pro nové varianty

Obě nové varianty (`varianta-c1-*`, `varianta-c2-*`) mají vyřešeno:

1. **H1** = služba + město, značka přesunuta do `<p>`
2. **Title / description / canonical / Open Graph / Twitter Card**
3. **Loga** — PNG wordmark v hlavičce i patičce, funkční favicon
4. **Preloader zrušen** — animace odkrytí jména zůstala, přesunutá do hero, nic neblokuje
5. **Karty služeb**: hover odkryje hlubší popis, klik vede na podstránku služby
6. **Tři podstránky služeb** s vlastním title, H1, FAQ a `Service` schématem
7. **Ceník** jako plnohodnotná sekce
8. **JSON-LD** rozšířené: `HealthAndBeautyBusiness` + `Service` + `FAQPage` + `BreadcrumbList`
9. **Víc scrollu** a jeden **wow moment** — u každé varianty jiný, viz jejich hlavičkový komentář
10. `overflow-x:clip` místo `hidden`, opravený `tel:`, žádný mrtvý odkaz

Neřešitelné bez klienta zůstává: **adresa, ceny, telefon, e-mail, seznam benefitních
programů a vektorové logo v křivkách.** Všude jsou viditelné `[DOPLNIT]`.
