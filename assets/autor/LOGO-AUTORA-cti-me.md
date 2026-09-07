# Logo TH. — vektorová rekonstrukce

Logo bylo zrekonstruováno z jediného dochovaného podkladu:
`MOJE-LOGO/portfolio-logo.png` (1108 × 689 px, z 8. 2. 2026).
Kopie zdroje je ve složce `Zdroj (original)`.

**Font nebylo potřeba dohledávat.** Tvary jsem změřil přímo z alfa kanálu
originálu s přesností na setiny pixelu a přepsal je do křivek. Písmena T a H
jsou přesné pravoúhlé bloky, tečka je obtažená po skutečném obrysu.
Od téhle chvíle je **master soubor SVG**, ne font — logo je tedy plně
nezávislé na tom, čím bylo původně vysázené.

Shoda s originálem: průměrná odchylka 0,005 z 255 úrovní krytí,
odchylku větší než 6 % má 46 pixelů z 763 412 (0,006 %) — jde výhradně
o vyhlazení hran. Vizuálně je rekonstrukce nerozeznatelná od předlohy.

---

## Barvy

| Použití | HEX | RGB | CMYK (orientačně) |
|---|---|---|---|
| Písmena TH | `#000000` | 0, 0, 0 | 0 / 0 / 0 / 100 |
| Tečka | `#EE316A` | 238, 49, 106 | 0 / 90 / 45 / 0 |
| Písmena na tmavém | `#FFFFFF` | 255, 255, 255 | — |

Růžová `#EE316A` je z originálu odečtená přesně, ne odhadem.

---

## Co je ve složce

### `SVG/` — hlavní formát, použij všude, kde to jde
| Soubor | Popis |
|---|---|
| `TH-logo-cerne.svg` | **základní verze** — černé TH, růžová tečka, průhledné pozadí |
| `TH-logo-bile.svg` | pro tmavá pozadí — bílé TH, růžová tečka |
| `TH-logo-mono-cerne.svg` | celé černé (fax, ryté, jednobarevný tisk) |
| `TH-logo-mono-bile.svg` | celé bílé |
| `TH-logo-mono-ruzove.svg` | celé růžové |
| `TH-logo-na-bilem-pozadi.svg` | s napevno bílým podkladem |
| `TH-logo-na-cernem-pozadi.svg` | s napevno černým podkladem |
| `ctvercove (avatar)/` | čtvercový ořez pro profilovky a favicon |

### `PNG/` — každá varianta v 256 / 512 / 1024 / 2048 px šířky
Průhledné pozadí (kromě verzí „na … pozadí“). Čtvercové v 512 / 1024 / 2048 px.

PNG jsou vyrenderované analyticky z křivek, ne zvětšené z originálu —
i verze 2048 px je ostrá.

---

## Rozměry a proporce

Poměr stran **1062,3 : 446,0 ≈ 2,382 : 1**

| Prvek | Hodnota (v jednotkách SVG) |
|---|---|
| Výška verzálek | 438 |
| Tloušťka tahu | 132–132,8 |
| Šířka T | 379 |
| Šířka H | 415,3 |
| Příčka T (výška) | 103 |
| Příčka H (výška) | 112 |
| Tečka | 145,3 × 122,1 (širší než vyšší) |
| Mezera H → tečka | 70 |
| Přetah tečky pod účaří | 8 |

Tečka **není kruh** — je to zploštělý ovál a mírně přetahuje pod účaří
písmen. To je záměr, neopravuj to.

---

## Zásady použití

- **Ochranná zóna:** kolem loga nech volný prostor alespoň ve výšce příčky
  H (112 jednotek ≈ 10,5 % šířky loga). Čtvercové verze ji už mají v sobě.
- **Minimální velikost:** 80 px šířky na displeji, 20 mm v tisku.
- Logo **needituj v PNG** — vždy vyjdi ze SVG.
- Neměň rozestupy mezi T, H a tečkou a nedeformuj poměr stran.
- Na barevném pozadí použij mono verzi, ne základní — růžová tečka
  na barevném podkladu se ztrácí.

---

## Poznámka k původnímu fontu

Původní font se mi identifikovat nepodařilo a z jednoho PNG to spolehlivě
nejde. Podle kreseb šlo o velmi tučný groteskní řez s naprosto plochými,
nemodulovanými tahy a pravoúhlými zakončeními. Pokud bys někdy potřeboval
dosadit další znaky ve stejném stylu, hledej řezy Black / Heavy
neo-grotesků — ale pro samotné logo to už není potřeba, křivky jsou hotové.
