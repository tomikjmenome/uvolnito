# Uvolni.to — design systém a pravidla projektu

Web pro masážní studio **Uvolni.to** (Lukáš Václavek). Kontext, konkurence a předlohy: viz `PROJEKT.md`.

> **Pro varianty G a novější platí navíc `SUPERPROMPT.md`** — zpřísňuje tenhle dokument
> (celoobrazovkové hero, povolený WebGL v mezích, autokontrola před odevzdáním).
> Kde si oba dokumenty odporují, vyhrává přísnější pravidlo.

---

## 1. Designový směr — ZÁVAZNÝ

Klient ohodnotil předlohy takto (7 = nejvyšší): **Antara Spa 7** · Tawan 6 · Saltwater 6 · Urban 6 · PRESS 5 · Be Relax 5 · Refuge 5 · RUB 3 · Remedy 3 · Heights 3 · Salon Elite 1.

**Z toho plyne směr: vzdušná, elegantní, scroll-driven estetika.**

| Ano | Ne |
|---|---|
| Hluboká zelená + krémová, přírodní tóny (Antara) | Tmavé klinické brandování (RUB — hodnoceno 3) |
| Velký whitespace, nadpis jako grafický prvek (Saltwater) | Husté informační bloky |
| Plynulé scroll-driven odkrývání, parallax, storytelling | Statická stránka s fade-in „na efekt" |
| Jasná struktura a ceny na kartách (Tawan) | Skrytý ceník, rezervace jen telefonem (Salon Elite — 1) |
| Booking flow ve 3 krocích + trust vrstva (Urban) | Odchod na cizí doménu bez kontextu |

Tón: **elegantní a klidný, ne klinický a ne éterický wellness kýč.**

---

## 2. Design tokeny — POUŽÍVAT DOSLOVA

Nevymýšlet vlastní barvy. Nepoužívat výchozí Tailwind paletu (`gray-500`, `emerald-600` atd.) — vždy tyto tokeny.

```css
:root {
  /* ZÁKLAD — odvozeno přímo z loga klienta (assets/ostatni/Kopie-navrhu-…png).
     Nejde o odhad: hodnoty jsou změřené z pixelů barevné verze loga. */
  --c-forest:    #304F47;  /* ZNAČKOVÁ ZELENÁ — hlavičky, patička, tmavé sekce, text loga */
  --c-sand:      #CBBBA0;  /* ZNAČKOVÁ PÍSKOVÁ — plamínek v logu, akcenty, oddělovače */

  /* Odvozené odstíny (dopočítané ze dvou barev výše) */
  --c-forest-dk: #223A34;  /* tmavší zelená — patička, overlay na fotkách */
  --c-moss:      #4A6E64;  /* světlejší zelená — hover stavy, sekundární plochy */
  --c-sage:      #8FA79E;  /* tlumená — ikony, linky, sekundární text na tmavé */
  --c-sand-lt:   #E4DCCC;  /* světlá písková — alternující sekce, karty */
  --c-cream:     #F7F3EC;  /* hlavní pozadí stránky */
  --c-ink:       #1B211F;  /* text */
  --c-ink-soft:  #55605C;  /* sekundární text */
  --c-white:     #FFFFFF;

  /* Terakota = JEDINÝ teplý akcent navíc, max 1× na obrazovku.
     Používat jen tam, kde písková nestačí (např. cena v akci). */
  --c-terra:     #B5714E;

  /* Typografie */
  --font-display: "Fraunces", "Playfair Display", Georgia, serif;
  --font-body:    "Inter", system-ui, -apple-system, sans-serif;

  /* Fluidní škála */
  --fs-hero: clamp(2.75rem, 7vw, 6rem);
  --fs-h2:   clamp(1.875rem, 4vw, 3.25rem);
  --fs-h3:   clamp(1.25rem, 2vw, 1.75rem);
  --fs-body: clamp(1rem, 1.05vw, 1.125rem);
  --fs-small: 0.875rem;

  /* Rytmus — sekce dýchají, whitespace je záměr */
  --space-section: clamp(5rem, 12vh, 10rem);
  --space-block:   clamp(2rem, 5vh, 4rem);
  --radius:        4px;   /* téměř ostré rohy, žádné pilulky */
  --maxw:          1280px;
  --maxw-text:     68ch;
}
```

**Typografická pravidla**
- Nadpisy: `--font-display`, `letter-spacing: -0.02em`, `line-height: 1.05`.
- Hero claim: velký, může být rozložený přes celou šířku jako grafický prvek (model Saltwater).
- Body text nikdy širší než `--maxw-text`.
- Kapitálky (`text-transform: uppercase`, `letter-spacing: 0.12em`) jen na štítky a nadřazené popisky, nikdy na odstavce.

**Poznámka k barvám:** značková zelená `#304F47` je **chladná, do teala**, ne olivová. Nezaměňovat za `#425E40` z Antary — ta byla jen inspirační referencí, dokud jsme neměli logo. Písková `#CBBBA0` je jediný teplý protipól; kombinace zelená + písek + krémová je celá identita.

**Obrazové podklady:** `assets/` — soupis, kvalita a omezení v `assets/INVENTURA.md`. **Všechny existující fotky jsou na výšku (9:16).** Do varianty F to znamenalo split hero; od varianty G je hero povinně celoobrazovkové (§3), takže se vybírá jen ze snímků, které snesou tvrdý horizontální ořez bez uříznutých obličejů — ověřený kandidát je `foto/…17.42.34.jpeg` (lávové kameny). Ořez vždy odsimulovat, ne odhadnout. Před ostrým spuštěním dofotit širokoúhlé záběry. Nikdy nepoužívat nic z `assets/_sablona-nepouzivat/` (cizí stocky ze šablony).

**Zakázáno**
- Purpurovo-modré gradienty, glassmorphism, neonové akcenty, tmavý režim „protože to jde".
- Emoji jako ikony. Stock fotky. Placeholder `lorem ipsum` v odevzdávaném kódu.
- Více než jeden terakotový prvek na obrazovku.

---

## 3. Motion / scroll — jak animovat

Máme nainstalované skills v `.claude/skills/` (viz sekce 5). **Před psaním jakékoli scroll animace načíst `gsap-scrolltrigger`**, pro timeline `gsap-timeline`, pro výkon `gsap-performance`.

**Stack:** GSAP 3 + ScrollTrigger, plus Lenis pro smooth scroll (moderní nástupce Locomotive Scroll — `locomotive-scroll` skill použít jen jako koncepční referenci, ne pro volbu knihovny).

**HERO — závazné od varianty G** *(rozhodnutí klienta, 5. 9. 2026)*
Hero je **vždy přes celou obrazovku** (`height:100svh; min-height:620px`) s **obrázkem na celé ploše**
v pozadí — později se vymění za video. Ne split layout, ne fotka v rámečku vedle textu.
V HTML je připravený zakomentovaný `<video>` se stejnou třídou, aby šla záměna udělat na jednom místě.
Nad fotkou vždy tmavý overlay, kontrast textu min. 4,5:1. Text hero je v HTML, ne v JS.
Detaily a checklist: `SUPERPROMPT.md` §1.

**Pravidla**
1. Animovat **jen `transform` a `opacity`**. Nikdy `top/left/width/height` ve scroll animaci.
2. Každý ScrollTrigger má `id` a při unmount se killne. `markers: true` nikdy neposílat do produkce.
3. `scrub` používat pro vazbu na pozici scrollu; hodnota `1`–`1.5` (plynulé dojíždění), ne `true`.
4. **`prefers-reduced-motion: reduce` musí vypnout veškerý parallax a scrub.** Obsah zůstane plně čitelný a dostupný bez JS.
5. Pinned sekce max **jedna** na celém webu (kandidát: „Jak masáž probíhá"). Víc = únava.
6. Hero se nesmí animovat tak, aby zdržel LCP. Text hero je v HTML, ne v JS.
7. Na mobilu parallax a pin vypnout přes `ScrollTrigger.matchMedia()` — dotykové zařízení + pin = problémy.

**Kde scrollytelling dává smysl (a kde ne)**
- ✅ „Jak masáž probíhá" — pinovaná sekce, kroky 1→4 odkrývané scrubem.
- ✅ Odkrývání sekcí služeb, jemný parallax na fotkách studia (max 10–15 % posunu).
- ✅ Počítadla v trust pruhu (roky praxe, počet klientů) — jednorázově, `once: true`.
- ❌ Ceník, kontakt, rezervace, otevírací doba. **Konverzní obsah se neanimuje** — musí být okamžitě čitelný.

---

## 4. Technická pravidla

- **Mobile-first.** Rozvržení navrhovat od 375 px nahoru.
- Cíle: **LCP < 2,5 s**, CLS < 0,1, INP < 200 ms.
- Obrázky: `<img>` s `width`/`height` (proti CLS), `loading="lazy"` mimo hero, formát AVIF/WebP.
- Fonty: `font-display: swap`, preload jen řezu použitého v hero.
- Sémantické HTML, viditelný `:focus-visible`, kontrast min. **4,5:1** (krémová/zelená to splní, hlídat `--c-sage` na krémové — používat jen na velký text).
- Texty **v češtině**, česká typografie: uvozovky „…", nezlomitelná mezera po jednoznakových předložkách, `1 500 Kč` (mezera před Kč).
- Strukturovaná data `LocalBusiness` + `Service` na každé stránce služby.

---

## 5. Nainstalované skills (`.claude/skills/`)

| Skill | Zdroj | Kdy načíst |
|---|---|---|
| `gsap-scrolltrigger` | GreenSock (oficiální, MIT) | scroll animace, pin, parallax, scrub |
| `gsap-core`, `gsap-timeline` | GreenSock | tweeny, sekvence, orchestrace |
| `gsap-plugins` | GreenSock | SplitText, ScrollSmoother, Flip |
| `gsap-performance` | GreenSock | optimalizace, když drhne FPS |
| `gsap-utils` | GreenSock | `gsap.utils` helpery |
| `gsap-react`, `gsap-frameworks` | GreenSock | jen při React/Next implementaci (cleanup, `useGSAP`) |
| `modern-web-design` | freshtechbro (MIT) | principy layoutu, typografie, a11y |
| `scroll-reveal-libraries` | freshtechbro (MIT) | jednoduché reveal efekty bez GSAP |
| `motion-framer` | freshtechbro (MIT) | jen při React implementaci |
| `locomotive-scroll` | freshtechbro (MIT) | koncepční reference ke smooth scrollu |

Licence k atribuci: `LICENSE-gsap-skills.txt`, `LICENSE-claudedesignskills.txt`.

**Vědomě neinstalováno:** Three.js, React Three Fiber, Babylon.js, PlayCanvas, A-Frame/WebXR, PixiJS, Blender, Substance, Spline, Rive. Plné 3D scény jsou na masérském webu balast, který jen zhorší výkon.

**Revize 3D politiky (5. 9. 2026):** plošný zákaz padá, hranice se posouvá — ne ruší.
Povolený je **ručně psaný WebGL shader nad hero fotkou** (displacement, zrno, dech) a **CSS 3D**
na kartách a předělech. Rozpočet: max. 8 kB inline, **nula knihoven navíc** oproti GSAP + ScrollTrigger + Lenis.
Povinné pojistky (fotka pod canvasem, vypnuto na mobilu i při `reduced-motion`, ošetřený
`webglcontextlost`, stropovaný DPR, pauza mimo viewport) jsou v `SUPERPROMPT.md` §2 — bez nich se shader nepíše.
Knihovny z tabulky výše zůstávají zakázané.

---

## 6. Pracovní postup pro zadání „udělej variantu X"

Když klient zadá variantu předlohy:

1. **Načíst `gsap-scrolltrigger`** (a `modern-web-design`), než napíšu první řádek.
2. Držet tokeny ze sekce 2 **doslova** — žádné vlastní barvy, žádná výchozí Tailwind paleta.
3. Dodat **jeden samostatný HTML soubor** s inline CSS/JS, aby šel otevřít bez buildu.
4. Reálné české texty, ne lorem ipsum. Ceny jako `[DOPLNIT]` tam, kde je klient ještě nedodal.
5. Vždy zahrnout: sticky CTA „Rezervovat", ceník s délkou i cenou, adresu, blok benefitů.
6. Na konci uvést v jedné větě, čím se varianta liší od ostatních.

---

## 7. Nezodpovězené vstupy — blokují finální web

Bez těchto údajů se dělají jen vizuální varianty, ne ostrý web:
- **adresa studia** (blokuje lokální SEO i sekci „Kde nás najdete")
- **ceník** (délky + ceny všech tří služeb)
- seznam přijímaných benefitních programů
- existence loga / brand manuálu
- dostupnost profi fotografií

Plný seznam otázek: `PROJEKT.md`, sekce 4.
