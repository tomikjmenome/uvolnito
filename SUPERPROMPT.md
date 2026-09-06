# SUPERPROMPT — „wow" varianty pro uvolni.to

Závazné zadání pro každou novou designovou variantu od varianty **G** dál.
Nadřazené dokumenty: `CLAUDE.md` (tokeny, motion, technika) a `PROJEKT.md` (obsah, positioning).
**Tenhle soubor nepřepisuje CLAUDE.md — zpřísňuje ho.** Kde si odporují, platí přísnější pravidlo.

---

## 0. Jedna věta, o co jde

> Návštěvník má do tří vteřin cítit, že si sedl do tmavé místnosti, kde se o něj někdo postará —
> a do třiceti vteřin mít po ruce cenu, adresu a tlačítko Rezervovat.

Wow slouží konverzi. Když si efekt a rezervace překážejí, vyhrává rezervace.

---

## 1. HERO — tvrdé pravidlo

**Hero je vždy přes celou obrazovku, s obrázkem (později videem) v pozadí.** Bez výjimek.

```
height: 100svh;           /* svh, ne vh — mobilní lišta jinak ustřihne CTA */
min-height: 620px;        /* na nízkých landscape displejích nesmí zkolabovat */
```

- Pozadí = **jeden snímek přes celou plochu**, `object-fit: cover`. Žádný split layout,
  žádný krémový podklad, žádná fotka v rámečku vedle textu.
- **Přechod na video se dělá záměnou jednoho elementu, ne přestavbou hero.** V HTML je proto
  vždy připravený zakomentovaný `<video>` se stejnou třídou a stejným `object-position`,
  aby ho klient/vývojář jen odkomentoval:
  ```html
  <img id="heroMedia" src="…" …>
  <!-- AŽ BUDE VIDEO: nahradit <img> tímhle, nic dalšího neměnit
  <video id="heroMedia" class="hero-media" autoplay muted loop playsinline
         poster="…" preload="metadata"><source src="…" type="video/mp4"></video>
  -->
  ```
- Nad fotkou je **vždy tmavý overlay** (`--c-forest-dk` gradient, min. 55 % krytí v místě textu).
  Kontrast textu se měří, ne odhaduje: minimálně **4,5:1**.
- **Text hero je v HTML, nikdy ho negeneruje JS.** LCP element = hero fotka nebo hero nadpis;
  musí být pod 2,5 s i bez dokončeného WebGL.
- V hero je právě **jedno primární CTA** („Rezervovat termín"). Sekundární odkaz smí být textový.
- Na mobilu (< 900 px) se hero nezmenšuje na půl obrazovky — zůstává celoobrazovkové,
  jen se vypne parallax a WebGL.

**Fotky jsou všechny na výšku (9:16).** Full-bleed hero se proto staví jen ze snímků,
které snesou tvrdý horizontální ořez — tj. bez uříznutých obličejů. Ověřený kandidát:
`assets/foto/WhatsApp-Image-2026-03-08-at-17.42.34.jpeg` (lávové kameny, horizontální kompozice,
tmavé kraje). Před použitím jiného snímku ořez odsimulovat, ne odhadnout.

---

## 2. 3D a WebGL — co je povoleno

CLAUDE.md §5 dřív 3D plošně zakazoval. **Nově platí toto:**

| Povoleno | Zakázáno |
|---|---|
| Raw WebGL shader nad hero fotkou (displacement, zrno, jemné vlnění) | Three.js, R3F, Babylon, Spline, importované 3D modely |
| CSS 3D (`perspective`, `preserve-3d`) na kartách a předělech | 3D objekty, které nic neznamenají („plovoucí koule") |
| Canvas 2D pro jemné textury a přechody | Fyzikální simulace, částicové systémy nad 2 000 částic |

**Rozpočet:** veškerý 3D/WebGL kód dohromady **max. 8 kB inline**, **nula externích knihoven navíc**
oproti GSAP + ScrollTrigger + Lenis. Shader se píše ručně; jde o jeden fullscreen quad, ne o scénu.

**Povinné pojistky u každého WebGL prvku:**
1. Pod canvasem leží **skutečný `<img>`**, který je vidět, dokud shader nenaběhne. Canvas se do něj
   prolne (`opacity` 0 → 1) až po `texture upload`. Bez JS, bez WebGL, při chybě kontextu
   nebo při chybějící kompilaci shaderu **zůstane fotka** a stránka je kompletní.
2. `prefers-reduced-motion: reduce` → **canvas se vůbec nevytvoří.**
3. `pointer: coarse` nebo šířka < 900 px → **canvas se vůbec nevytvoří.** Mobil dostane fotku.
4. `webglcontextlost` → canvas se skryje, fotka se vrátí. Žádná bílá díra.
5. `devicePixelRatio` se stropuje na **2**, render se pauzuje přes `IntersectionObserver`,
   jakmile hero opustí viewport. Žádná smyčka `requestAnimationFrame` běžící na pozadí.

Efekt musí dávat **významový** smysl: uvolnění = vlnění, dech, teplo. Ne technologická exhibice.

---

## 3. Scroll — co smí a co ne

Platí `CLAUDE.md` §3 beze změny, plus:

- **Jedna pinovaná sekce na celý web.** Ne dvě. Kandidát: „Jak masáž probíhá".
- Animuje se **jen `transform` a `opacity`**. `scrub: 1`–`1.5`, nikdy `true`.
- Každý ScrollTrigger má `id`. `markers` nikdy neodcházejí ze složky.
- Parallax a pin se na dotykových zařízeních vypínají přes `gsap.matchMedia()`.
- **Konverzní obsah se neanimuje vůbec:** ceník, adresa, otevírací doba, kontakt, rezervační blok.
  Musí být čitelné okamžitě a bez JS.
- Vstupní odkrytí sekce má `once: true`. Nic se při scrollu nahoru „nepřehrává znovu".

---

## 4. Obsah — co v každé variantě musí být

Bez výjimky, v tomhle pořadí smyslu (ne nutně tomhle pořadí na stránce):

1. Hero (viz §1) — claim + jedno CTA
2. Trust vrstva — konkrétní čísla, ne adjektiva
3. Tři služby — **vždy délka i cena** na kartě
4. Jak masáž probíhá — krok za krokem
5. O Lukášovi — portrét, nosná sekce, ne odkaz v patičce
6. Reference — se zdrojem
7. Zaměstnanecké benefity — Edenred, Benefit plus (silný USP, nesmí zapadnout)
8. Dárkové poukazy
9. Kde nás najdete — adresa, doprava, parkování
10. Rezervace — Reservio v místě, ne odchod na cizí doménu
11. Patička — plná navigace, IČO, GDPR
12. Sticky CTA „Rezervovat" — na mobilu spodní lišta

Texty **česky, reálné, s českou typografií** („uvozovky", nezlomitelná mezera po předložkách,
`1 500 Kč`). Chybějící údaje od klienta se píší jako viditelné **`[DOPLNIT]`** — nikdy se
nevymýšlejí ceny ani adresa. Žádné lorem ipsum.

---

## 5. Technická podoba dodávky

- **Jeden samostatný `.html` soubor** s inline CSS i JS, otevíratelný dvojklikem bez buildu.
- Externě jen: Google Fonts, GSAP, ScrollTrigger, Lenis. Nic dalšího.
- Tokeny z `CLAUDE.md` §2 **doslova**. Žádné vlastní hex hodnoty, žádná Tailwind paleta.
- Sémantické HTML, `:focus-visible`, `alt` u všech fotek, `width`/`height` u všech `<img>`.
- Cíle: **LCP < 2,5 s · CLS < 0,1 · INP < 200 ms.**
- Název souboru: `varianta-<písmeno>-<jednoslovný-název>.html`.

---

## 6. Autokontrola před odevzdáním

Projít bod po bodu, žádný nepřeskočit:

- [ ] Hero je `100svh` a má fotku na celé ploše
- [ ] V hero je zakomentovaný `<video>` připravený k záměně
- [ ] Kontrast hero textu ≥ 4,5:1 proti nejsvětlejšímu místu fotky pod ním
- [ ] Vypnutý JS → stránka je kompletní a čitelná; hero má fotku
- [ ] `prefers-reduced-motion` → žádný canvas, žádný scrub, žádný pin
- [ ] Mobil 375 px → hero drží, CTA je nad ohybem, canvas neexistuje
- [ ] Právě jedna pinovaná sekce na celém dokumentu
- [ ] Ceník, adresa a rezervace nejsou animované
- [ ] `grep markers` a `grep console.log` → nula výskytů
- [ ] Žádná barva mimo tokeny (`grep -oE '#[0-9a-fA-F]{3,8}'` → jen hodnoty z `CLAUDE.md`)
- [ ] Terakota `--c-terra` max 1× na obrazovku
- [ ] Nic z `assets/_sablona-nepouzivat/`
- [ ] Na konci odpovědi jedna věta: čím se varianta liší od ostatních

---

## 7. Čemu se vyhnout, i když to „vypadá dobře"

Purpurovo-modré gradienty · glassmorphism · neon · emoji místo ikon · stock fotky ·
tmavý režim „protože to jde" · preloader delší než 800 ms · horizontální scroll na mobilu ·
text generovaný JavaScriptem · více než jedna terakota na obrazovku ·
efekt, který zdrží první vykreslení.
