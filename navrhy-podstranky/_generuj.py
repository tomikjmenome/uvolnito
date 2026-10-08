#!/usr/bin/env python3
"""Generátor návrhů podstránek masáží.

Šablona = masaz-lavovymi-kameny.html (hlava se styly, hlavička, patička).
Obsah <main>, meta tagy, menu a JSON-LD se skládají z dat níže.

PRAVIDLO OBSAHU: jen texty ověřené 2. 10. 2026 —
  R  = popis služby v Reserviu (uvolni-to.reservio.com/services)
  HP = stávající homepage (index.html)
  OP = pravni-informace.html
  REV = doslovná recenze z uvolni-to.reservio.com/reviews
Co ověřit nešlo, je lorem ipsum (na přání klienta, jen pro návrhy).

Spuštění: python3 _generuj.py   (přepíše všech šest HTML v této složce)
"""
import html as H
import json
import pathlib
import re

DIR = pathlib.Path(__file__).parent
TEMPLATE = DIR / "_sablona.html"
BASE = "https://uvolnito.cz/"
RESV = "https://uvolni-to.reservio.com"

L = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore."
LS = "Lorem ipsum dolor sit amet, consectetur adipiscing elit."

# ── Společné ověřené bloky ─────────────────────────────────────────
KROK = {  # HP, sekce „Od dveří po odchod“ — doslova
    "konzultace": ("Krátká konzultace", "Zeptám se, co tě trápí, kde máš bolest a&nbsp;na co si dát pozor."),
    "tlak": ("Nastavení tlaku", "Během prvních minut ladíme intenzitu. Kdykoli řekneš, přitlačím nebo povolím."),
    "masaz": ("Samotná masáž", "Soustředěná práce. Žádné hlídání hodin, žádný spěch na&nbsp;dalšího klienta."),
    "potom": ("Co dělat potom", "Dostaneš pár konkrétních tipů na doma&nbsp;— protažení, pitný režim, kdy přijít znovu."),
    "lorem": ("Lorem ipsum", L),
}
FAQ = {  # HP (Časté dotazy) a OP §6 — doslova nebo bez změny smyslu
    "bolet": ("Musí masáž bolet, aby zabrala?",
              "Ne. Bolest je informace, ne cíl. Tlak nastavíme během prvních minut tak, aby byl na&nbsp;hraně příjemného, a&nbsp;kdykoli během masáže si můžeš říct o&nbsp;míň. Sval, který se brání, se stejně neuvolní."),
    "casto": ("Jak často má smysl chodit?",
              "U&nbsp;sedavé práce a&nbsp;tuhnoucí šíje obvykle jednou za&nbsp;tři až čtyři týdny. U&nbsp;sportovní regenerace podle tréninkového cyklu. Na&nbsp;konci každé masáže dostaneš odhad, kdy má smysl přijít znovu."),
    "sebou": ("Co si mám vzít s&nbsp;sebou?",
              "Nic. Ručníky, prostěradlo i&nbsp;olej jsou tady. Přijď radši o&nbsp;pár minut dřív, ať se dá v&nbsp;klidu projít, co tě trápí, a&nbsp;nekrátí to čas na&nbsp;lehátku."),
    "svlek": ("Musím být úplně svlečený?",
              "Ne. Odkrývá se vždy jen partie, na&nbsp;které se zrovna pracuje, zbytek těla je přikrytý. Spodní prádlo si necháváš."),
    "benefity": ("Můžu zaplatit benefity?",
                 "Ano. Přijímám Edenred a&nbsp;Benefit&nbsp;plus. Stačí to zmínit při&nbsp;rezervaci, platí se až na&nbsp;místě."),
    "darovat": ("Dá se masáž darovat?",
                "Ano. <a href=\"../poukazy.html\" class=\"ul\" style=\"color:var(--c-forest)\">Dárkový poukaz objednáš online</a> na&nbsp;masáž i&nbsp;na&nbsp;částku, přijde e-mailem jako PDF. Platí šest měsíců od&nbsp;vystavení."),
    "madero_kdo": ("Je maderoterapie vhodná pro každého?",
                   "Většinou ano, ale má svá omezení&nbsp;— čerstvá poranění, křečové žíly v&nbsp;místě aplikace, těhotenství nebo onkologická léčba jsou důvod se předem poradit. Napiš mi, co řešíš, a&nbsp;řeknu rovnou, jestli má smysl přijít."),
}
POZOR = [  # OP §7
    "horečka nebo akutní infekce, první trimestr těhotenství",
    "úraz, operace nebo zánět",
    "křečové žíly, kožní onemocnění, onkologická léčba, léky na&nbsp;ředění krve",
]
POZOR_MADERO = [  # OP §7 + HP FAQ
    "horečka nebo akutní infekce, první trimestr těhotenství",
    "čerstvá poranění, křečové žíly v&nbsp;místě aplikace",
    "onkologická léčba, kožní onemocnění, léky na&nbsp;ředění krve",
]

# ── Masáže ─────────────────────────────────────────────────────────
M = [
  dict(slug="relaxacni-masaz", soubor="relaxacni-masaz.html", menu="Relaxační masáž", od="od 990 Kč",
       num="01 — Klid", foto="17-44-40", big="1066", wh=(1066, 1600), pos="40%",
       alt="Relaxační masáž s teplým olejem",
       title="Relaxační masáž Brno — 60, 90 i 120 min od 990 Kč | uvolni.to",
       desc="Klidnější tempo, olejová masáž. Když cítíš stres. 60, 90 nebo 120 minut od 990 Kč v masážním studiu v Brně u Lidické, platba i Edenred a Benefit plus.",
       h1="Relaxační masáž v&nbsp;<em>Brně</em>",
       lead="Klidnější tempo, olejová masáž. Když cítíš stres.",                     # R
       fakta=[("Délka", "60 / 90 / 120&nbsp;min"), ("Cena", "od 990&nbsp;Kč"), ("Tempo", "klidnější"), ("Nejlépe na", "stres")],
       karta="Klidnější tempo, olejová masáž. Když cítíš stres.",
       smysl_h2="Kdy si dát relaxační masáž",
       smysl=["Když cítíš stres", "Lorem ipsum dolor", "Lorem ipsum dolor"],
       proces_h2="Od dveří po&nbsp;odchod", kroky=["konzultace", "tlak", "masaz", "potom"],
       ceny=[("60 minut", 990, LS), ("90 minut", 1290, LS), ("120 minut", 1590, LS)],
       pozor=POZOR,
       lukas_h2="Masíruju tě pokaždé já", lukas_alt="Portrét Lukáše Václavka ve studiu",
       rev=("Nejlepší místo na&nbsp;masáž a&nbsp;odpočinek, prostě není co dodat. Profesionál v&nbsp;oboru s&nbsp;fakt úžasnou pozitivní energií. Vracím se opakovaně.", "Alexandra", "16.&nbsp;7.&nbsp;2026"),
       faq_h2="Na co se ptáš u&nbsp;relaxační masáže",
       faq=["sebou", "svlek", ("60, 90, nebo 120 minut?", None), "benefity", "darovat"]),

  dict(slug="regeneracni-masaz", soubor="regeneracni-masaz.html", menu="Regenerační masáž", od="od 990 Kč",
       num="02 — Úleva", foto="18-06-27", big="1066", wh=(1066, 1600), pos="40%",
       alt="Regenerační masáž šíje a ramen",
       title="Regenerační masáž Brno — 60, 90 i 120 min od 990 Kč | uvolni.to",
       desc="Hloubkovější práce na svalech. Když tě bolí tělo. 60, 90 nebo 120 minut od 990 Kč v masážním studiu v Brně u Lidické, platba i Edenred a Benefit plus.",
       h1="Regenerační masáž v&nbsp;<em>Brně</em>",
       lead="Hloubkovější práce na&nbsp;svalech. Když tě bolí tělo.",               # R
       fakta=[("Délka", "60 / 90 / 120&nbsp;min"), ("Cena", "od 990&nbsp;Kč"), ("Tempo", "hloubkovější"), ("Nejlépe na", "bolavé svaly")],
       karta="Hloubkovější práce na&nbsp;svalech. Když tě bolí tělo.",
       smysl_h2="Kdy si dát regenerační masáž",
       smysl=["Když tě bolí tělo", "Při sportu a&nbsp;tréninku", "Lorem ipsum dolor"],  # R, HP FAQ (sportovní regenerace)
       proces_h2="Od dveří po&nbsp;odchod", kroky=["konzultace", "tlak", "masaz", "potom"],
       ceny=[("60 minut", 990, LS), ("90 minut", 1290, LS), ("120 minut", 1590, LS)],
       pozor=POZOR,
       lukas_h2="Masíruju tě pokaždé já", lukas_alt="Portrét Lukáše Václavka ve studiu",
       rev=("Přišel jsem na&nbsp;funkční sportovní masáž jako vždycky a&nbsp;byla perfektní. Efektivní uvolnění všeho, co mě na&nbsp;zádech bolelo. Lukáš je profík.", "Šimon", "10.&nbsp;7.&nbsp;2026"),
       faq_h2="Na co se ptáš u&nbsp;regenerační masáže",
       faq=["bolet", "casto", ("60, 90, nebo 120 minut?", None), "benefity", "darovat"]),

  dict(slug="masaz-lavovymi-kameny", soubor="masaz-lavovymi-kameny.html", menu="Lávové kameny", od="od 1 190 Kč",
       num="03 — Teplo", foto="17-42-34", big="1066", wh=(1066, 1600), pos="48%",
       alt="Nahřáté lávové kameny položené na zádech",
       title="Masáž lávovými kameny Brno — 60 a 90 min od 1 190 Kč | uvolni.to",
       desc="Teplo, které jde hloub než ruce. Klidnější tempo, když cítíš stres a potřebuješ prohřát svaly. 60 nebo 90 minut od 1 190 Kč, Brno u Lidické.",
       h1="Masáž lávovými kameny v&nbsp;<em>Brně</em>",
       lead="Teplo, které jde hloub než ruce. Klidnější tempo, když cítíš stres a&nbsp;potřebuješ prohřát svaly.",  # HP + R
       fakta=[("Délka", "60 / 90&nbsp;min"), ("Cena", "od 1&nbsp;190&nbsp;Kč"), ("Tempo", "klidnější"), ("Nejlépe na", "stres, prohřátí svalů")],
       karta="Teplo, které jde hloub než ruce. Když je záda potřeba nejdřív rozehřát.",
       smysl_h2="Kdy po&nbsp;kamenech sáhnout",
       smysl=["Když cítíš stres", "Když potřebuješ prohřát svaly", "Když je záda potřeba nejdřív rozehřát", "Lorem ipsum dolor"],
       proces_h2="Teplo, které jde hloub", kroky=["konzultace", "tlak", "lorem", "potom"],
       ceny=[("60 minut", 1190, LS), ("90 minut", 1490, LS)],
       pozor=POZOR,
       lukas_h2="Kameny ti na&nbsp;záda pokládám vždycky já", lukas_alt="Lukáš Václavek s lávovými kameny v dlaních",
       rev=("Skvělá masáž. Oceňuji individuální a&nbsp;velmi profesionální přístup, příjemnou komunikaci a&nbsp;skvělou péči. Rozhodně doporučuji.", "Julie", "31.&nbsp;7.&nbsp;2026"),
       faq_h2="Na co se ptáš u&nbsp;kamenů",
       faq=[("Nepálí kameny?", None),
            ("Je masáž s&nbsp;kameny silná?", "Lávové kameny mají klidnější tempo. Tlak nastavíme během prvních minut tak, aby byl na&nbsp;hraně příjemného, a&nbsp;kdykoli během masáže si můžeš říct o&nbsp;míň."),
            ("60, nebo 90 minut?", None), "benefity", "darovat"]),

  dict(slug="maderoterapie", soubor="maderoterapie.html", menu="Maderoterapie kombi", od="od 1 190 Kč",
       num="04 — Lymfa", foto="17-52-32", big="1354", wh=(1354, 2048), pos="36%",
       alt="Maderoterapie dřevěným válečkem",
       title="Maderoterapie Brno — kombi 60 a 90 min od 1 190 Kč | uvolni.to",
       desc="Přírodní technika pro podporu lymfy dřevěnými nástroji. Když řešíš otoky. 60 nebo 90 minut od 1 190 Kč, v 90 minutách i madero pleti. Brno u Lidické.",
       h1="Maderoterapie v&nbsp;<em>Brně</em>",
       lead="Práce dřevěnými nástroji. Přírodní technika pro podporu lymfy, když tělo zadržuje vodu a&nbsp;nohy večer otékají.",  # HP + R
       fakta=[("Délka", "60 / 90&nbsp;min"), ("Cena", "od 1&nbsp;190&nbsp;Kč"), ("Nástroje", "dřevěné"), ("Nejlépe na", "lymfa, otoky")],
       karta="Přírodní technika pro podporu lymfy. Když řešíš lymfu a&nbsp;otoky.",
       smysl_h2="Kdy po&nbsp;maderoterapii sáhnout",
       smysl=["Když tělo zadržuje vodu", "Když nohy večer otékají", "Lorem ipsum dolor"],
       proces_h2="Od dveří po&nbsp;odchod", kroky=["konzultace", "tlak", "lorem", "potom"],
       ceny=[("60 minut", 1190, LS), ("90 minut", 1490, "V&nbsp;ceně je i&nbsp;madero pleti.")],  # R
       pozor=POZOR_MADERO,
       lukas_h2="Masíruju tě pokaždé já", lukas_alt="Portrét Lukáše Václavka ve studiu",
       rev=("Masáž, co vás odvane do&nbsp;úplně jiného vesmíru — bez napětí, bez stresu, do&nbsp;naprostého uvolnění a&nbsp;myšlenkového vakua. Je to relaxační bomba pro tělo, mysl i&nbsp;duši. Maderoterapii a&nbsp;masáž ‚hrbu‘ doporučuji.", "Karin", "26.&nbsp;8.&nbsp;2026"),
       faq_h2="Na co se ptáš u&nbsp;maderoterapie",
       faq=["madero_kdo", ("Co je navíc v&nbsp;devadesáti minutách?", "V&nbsp;ceně devadesátiminutové maderoterapie kombi je i&nbsp;madero pleti."),
            "svlek", "benefity", "darovat"]),

  dict(slug="mangovy-ritual", soubor="mangovy-ritual.html", menu="Mangový rituál", od="1 890 Kč",
       num="05 — Rituál", foto="18-18-50", big="900", wh=(900, 1600), pos="52%",
       alt="Kamenné umyvadlo a přírodní dekorace ve studiu",
       title="Mangový rituál Brno — 120 minut za 1 890 Kč | uvolni.to",
       desc="Mangový peeling, maska s péčí o obličej, celotělová masáž hřejivým mangovým olejem a nápoj na závěr. 120 minut za 1 890 Kč v Brně u Lidické.",
       h1="Mangový rituál v&nbsp;<em>Brně</em>",
       lead="Peeling, maska, celotělová masáž mangovým olejem a&nbsp;nápoj na&nbsp;závěr. Dvě hodiny, kdy se nikam nejde.",  # HP
       fakta=[("Délka", "120&nbsp;min"), ("Cena", "1&nbsp;890&nbsp;Kč"), ("Obsahuje", "peeling, masku, masáž"), ("Na závěr", "mangový nápoj")],
       karta="Peeling, maska, celotělová masáž mangovým olejem a&nbsp;nápoj na&nbsp;závěr.",
       smysl_h2="Pro koho je rituál",
       smysl=["Když si chceš dopřát rozmazlování", "Když potřebuješ načerpat energii", "Lorem ipsum dolor"],  # R
       proces_h2="Dvě hodiny, kdy se nikam nejde",
       kroky=[("Mangový peeling", "Jemný peeling odstraní odumřelé buňky a&nbsp;pokožka zůstane hladká."),        # R
              ("Maska a&nbsp;péče o&nbsp;obličej", "Vyživující mangová maska během uvolňující péče o&nbsp;obličej dodá pleti svěžest."),
              ("Celotělová masáž", "Masáž hřejivým mangovým olejem, vůně manga a&nbsp;jemná hudba."),
              ("Mangový nápoj", "Na&nbsp;závěr osvěžující mangový nápoj.")],
       ceny=[("120 minut", 1890, "Peeling, maska s&nbsp;péčí o&nbsp;obličej, celotělová masáž a&nbsp;mangový nápoj.")],
       pozor=POZOR,
       lukas_h2="Celý rituál dělám já", lukas_alt="Portrét Lukáše Václavka ve studiu",
       rev=("Perfektní masáž, krásné prostředí, příjemný pan masér. Děkuji, přijdu zase.", "Pavla", "17.&nbsp;9.&nbsp;2026"),
       faq_h2="Na co se ptáš u&nbsp;rituálu",
       faq=[("Co všechno rituál obsahuje?", "Mangový peeling, vyživující mangovou masku s&nbsp;péčí o&nbsp;obličej, celotělovou masáž hřejivým mangovým olejem a&nbsp;na&nbsp;závěr mangový nápoj. Celé to trvá dvě hodiny."),
            ("Hodí se rituál jako dárek?", "Ano. <a href=\"../poukazy.html\" class=\"ul\" style=\"color:var(--c-forest)\">Dárkový poukaz objednáš online</a> na&nbsp;masáž i&nbsp;na&nbsp;částku, přijde e-mailem jako PDF. Platí šest měsíců od&nbsp;vystavení."),
            "sebou", "benefity"]),

  dict(slug="rychle-uvolneni", soubor="rychle-uvolneni.html", menu="Rychlé uvolnění", od="750 Kč",
       num="06 — Rychle", foto="18-06-56", big="1066", wh=(1066, 1600), pos="74%",
       alt="Lukáš Václavek při masáži zad",
       title="Rychlé uvolnění Brno — 30minutová masáž za 750 Kč | uvolni.to",
       desc="Krátká cílená úleva. Když není čas, ale tělo už protestuje. 30 minut za 750 Kč v masážním studiu v Brně u Lidické, platba i Edenred a Benefit plus.",
       h1="Rychlé uvolnění v&nbsp;<em>Brně</em>",
       lead="Krátká cílená úleva. Když není čas, ale tělo už protestuje.",           # R
       fakta=[("Délka", "30&nbsp;min"), ("Cena", "750&nbsp;Kč"), ("Zaměření", "cílená úleva"), ("Nejlépe na", "když není čas")],
       karta="Krátká cílená úleva. Když není čas, ale tělo už protestuje.",
       smysl_h2="Kdy stačí půl hodiny",
       smysl=["Když není čas", "Když tělo už protestuje", "Když tuhne šíje"],  # R, HP („Nejkratší cesta od ztuhlé šíje k úlevě“)
       proces_h2="Půl hodiny, krok za krokem", kroky=["konzultace", "tlak", "lorem", "potom"],
       ceny=[("30 minut", 750, "Nejkratší cesta od&nbsp;ztuhlé šíje k&nbsp;úlevě.")],   # HP
       pozor=POZOR,
       lukas_h2="Masíruju tě pokaždé já", lukas_alt="Portrét Lukáše Václavka ve studiu",
       rev=("Mám za&nbsp;sebou druhou masáž a&nbsp;už při té první jsem si říkal, že to nemůže být lepší. Ale stejně bylo. Naprosto perfektní přístup, síla masáže a&nbsp;zároveň opatrnost.", "Filip", "18.&nbsp;9.&nbsp;2026"),
       faq_h2="Na co se ptáš u&nbsp;rychlého uvolnění",
       faq=[("Co se stihne za&nbsp;30 minut?", None), "bolet", "benefity", "darovat"]),
]


def kc(n):
    return f"{n:,}".replace(",", "&nbsp;") + "&nbsp;Kč"


def plain(s):
    return H.unescape(re.sub(r"<[^>]+>", "", s)).replace(" ", " ")


def nbsp(s):
    return s.replace(" ", "&nbsp;") if len(s) < 14 else s


def picture(foto, big, wh, sizes, alt, extra=""):
    w, h = wh
    return (f'<picture><source type="image/webp" srcset="../assets/foto/opt/2026-03-08-at-{foto}-500.webp 500w, '
            f'../assets/foto/opt/2026-03-08-at-{foto}-900.webp 900w" sizes="{sizes}">'
            f'<img src="../assets/foto/opt/2026-03-08-at-{foto}-{big}.jpg" width="{w}" height="{h}" alt="{alt}"{extra}></picture>')


def render_main(m):
    me = m["slug"]
    o = []
    # HERO
    src_hero = f'../assets/foto/opt/2026-03-08-at-{m["foto"]}'
    hero_srcset = f'{src_hero}-500.webp 500w, {src_hero}-900.webp 900w' + (f', {src_hero}-{m["big"]}.webp {m["big"]}w' if m["big"] != "900" else "")
    fakta = "\n".join(f'      <div><dt>{k}</dt><dd>{v}</dd></div>' for k, v in m["fakta"])
    o.append(f'''<!-- ═══════════ HERO ═══════════ -->
<section class="b-hero on-dark">
  <div class="b-hero-bg">
    <picture>
      <source type="image/webp" srcset="{hero_srcset}" sizes="100vw">
      <img src="{src_hero}-{m["big"]}.jpg" width="{m["wh"][0]}" height="{m["wh"][1]}" alt="{m["alt"]}" fetchpriority="high" style="object-position:center {m["pos"]}">
    </picture>
  </div>
  <div class="wide b-hero-inner">
    <nav class="crumbs" aria-label="Drobečková navigace">
      <ol>
        <li><a href="../index.html">Domů</a></li>
        <li><a href="../index.html#sluzby">Masáže</a></li>
        <li><span aria-current="page">{m["menu"]}</span></li>
      </ol>
    </nav>
    <p class="b-kicker">{m["num"].upper()}</p>
    <h1>{m["h1"]}</h1>
    <p class="b-lead">{m["lead"]}</p>
    <dl class="b-ledger">
{fakta}
      <div class="b-go"><a href="{RESV}" class="btn btn-light" target="_blank" rel="noopener">Rezervovat</a></div>
    </dl>
  </div>
</section>

<svg class="arch-div from-forest-dk" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true" style="color:var(--c-cream)">
  <path d="M-8,128 L-8,84 L0,84 C420,84 596,0 720,0 C844,0 1020,84 1440,84 L1448,84 L1448,128 Z"></path>
</svg>
''')
    # KDY MÁ SMYSL
    li = "\n".join(f'      <li data-rv><div><h3>{h}</h3><p>{L}</p></div></li>' for h in m["smysl"])
    o.append(f'''<!-- ═══════════ KDY MÁ SMYSL ═══════════ -->
<section style="padding-top:clamp(2rem,6vh,4rem)">
  <div class="wide a-two">
    <div>
      <div class="rule tick" style="color:var(--c-forest)"><i></i><span>Pro koho</span><i></i></div>
      <h2 data-rv>{m["smysl_h2"]}</h2>
      <p class="intro" data-rv>{LS}</p>
    </div>
    <!-- Nadpisy bodů: Reservio / homepage. Odstavce čekají na Lukáše. -->
    <ol class="smysl">
{li}
    </ol>
  </div>
</section>
''')
    # PRŮBĚH
    kroky = []
    for i, k in enumerate(m["kroky"], 1):
        h, p = KROK[k] if isinstance(k, str) else k
        kroky.append(f'      <li data-rv><span>{i:02d}</span><h3>{h}</h3><p>{p}</p></li>')
    o.append(f'''<svg class="arch-div from-cream" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true" style="color:var(--c-sand-lt)">
  <path d="M-8,128 L-8,84 L0,84 C420,84 596,0 720,0 C844,0 1020,84 1440,84 L1448,84 L1448,128 Z"></path>
</svg>

<!-- ═══════════ JAK PROBÍHÁ ═══════════ -->
<section class="a-proces">
  <div class="wide">
    <div class="rule tick" style="color:var(--c-forest)"><i></i><span>Průběh</span><i></i></div>
    <h2 data-rv>{m["proces_h2"]}</h2>
    <ol class="kroky">
{chr(10).join(kroky)}
    </ol>
  </div>
</section>

<svg class="arch-div up from-sand" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true" style="color:var(--c-cream)">
  <path d="M-8,128 L-8,84 L0,84 C420,84 596,0 720,0 C844,0 1020,84 1440,84 L1448,84 L1448,128 Z"></path>
</svg>
''')
    # CENÍK + POZOR
    karty = "\n".join(f'''      <div class="cena">
        <span class="doba">{d}</span>
        <b>{kc(c)}</b>
        <p>{p}</p>
        <a href="{RESV}" class="btn btn-sm" target="_blank" rel="noopener">Rezervovat {d}</a>
      </div>''' for d, c, p in m["ceny"])
    pozor = "\n".join(f'        <li>{x}</li>' for x in m["pozor"])
    o.append(f'''<!-- ═══════════ CENÍK — konverzní obsah, bez animace. Ceny ověřené v Reserviu 2. 10. 2026 ═══════════ -->
<section id="cenik">
  <div class="wide">
    <div class="rule tick" style="color:var(--c-forest)"><i></i><span>Ceník</span><i></i></div>
    <div class="cenik-grid n{len(m["ceny"])}">
{karty}
    </div>
    <p class="cenik-pozn">
      <span>Hotově, převodem, Edenred i&nbsp;Benefit&nbsp;plus</span>
      <span>Zrušení zdarma do&nbsp;24&nbsp;hodin</span>
      <span>Jde darovat jako <a href="../poukazy.html" class="ul" style="color:var(--c-forest)">dárkový poukaz</a></span>
    </p>

    <!-- Zdroj: pravni-informace.html §7 (Zdravotní stav) -->
    <div class="pozor" data-rv>
      <h2>Kdy raději ne&nbsp;— nebo se nejdřív ozvi</h2>
      <ul>
{pozor}
      </ul>
      <p>Nejsem lékař a&nbsp;masáž není léčba. Když si nejsi jistý, napiš mi a&nbsp;řeknu rovnou, jestli má smysl přijít.
        Celé znění v&nbsp;<a href="../pravni-informace.html#op-zdravi">obchodních podmínkách</a>.</p>
    </div>
  </div>
</section>
''')
    # LUKÁŠ + REFERENCE
    rt, ra, rd = m["rev"]
    o.append(f'''<!-- ═══════════ LUKÁŠ ═══════════ -->
<section style="padding-top:0">
  <div class="wide a-lukas">
    <div class="arch" data-rv>
      <picture>
        <source type="image/webp" srcset="../assets/foto/opt/2026-03-08-at-18-12-14-500.webp 500w, ../assets/foto/opt/2026-03-08-at-18-12-14-900.webp 900w" sizes="(min-width:900px) 22rem, 18rem">
        <img src="../assets/foto/opt/2026-03-08-at-18-12-14-1400.jpg" width="1400" height="1815" alt="{m["lukas_alt"]}" loading="lazy">
      </picture>
    </div>
    <div>
      <p class="label" data-rv>Kdo masíruje</p>
      <h2 data-rv>{m["lukas_h2"]}</h2>
      <p data-rv>Jmenuju se Lukáš Václavek a&nbsp;ve&nbsp;studiu u&nbsp;Lidické masíruju sám. Žádné střídání masérů&nbsp;—
        kdo tě masíroval minule, ví, co tě bolelo.</p>
      <!-- Sem patří kurz / certifikát k této masáži — čeká na Lukáše -->
      <p data-rv>{L}</p>
      <div class="sig" data-rv><b>Lukáš Václavek</b>Masér a&nbsp;majitel studia · 5,0 na&nbsp;Reserviu z&nbsp;32 recenzí</div>
    </div>
  </div>
</section>

<svg class="arch-div from-cream" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true" style="color:var(--c-forest-dk)">
  <path d="M-8,128 L-8,84 L0,84 C420,84 596,0 720,0 C844,0 1020,84 1440,84 L1448,84 L1448,128 Z"></path>
</svg>

<!-- ═══════════ REFERENCE ═══════════ -->
<section class="deep on-dark a-ref" aria-label="Reference">
  <div class="wide">
    <!-- Doslovná citace z uvolni-to.reservio.com/reviews, ověřeno 2. 10. 2026 -->
    <blockquote class="rev-lead rev-center" data-rv>
      <p>&#8222;{rt}&#8220;</p>
      <cite><span class="badge">Reservio</span> {ra} &middot; {rd}</cite>
    </blockquote>
  </div>
</section>

<svg class="arch-div up from-forest-dk" viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true" style="color:var(--c-cream)">
  <path d="M-8,128 L-8,84 L0,84 C420,84 596,0 720,0 C844,0 1020,84 1440,84 L1448,84 L1448,128 Z"></path>
</svg>
''')
    # FAQ
    det = []
    for f in m["faq"]:
        q, a = FAQ[f] if isinstance(f, str) else f
        det.append(f'''      <details><summary><span class="faq-mark" aria-hidden="true"></span>{q}</summary>
        <div class="ans"><p>{a or L}</p></div></details>''')
    o.append(f'''<!-- ═══════════ FAQ ═══════════ -->
<section class="a-faq">
  <div class="wide a-two">
    <div>
      <div class="rule tick" style="color:var(--c-forest)"><i></i><span>Časté dotazy</span><i></i></div>
      <h2 data-rv>{m["faq_h2"]}</h2>
      <p class="intro" data-rv>Jiná otázka? <a href="#kontakt" class="ul" style="color:var(--c-forest)">Napiš mi.</a></p>
    </div>
    <div class="faq-list">
{chr(10).join(det)}
    </div>
  </div>
</section>
''')
    # DALŠÍ MASÁŽE
    cards = []
    for x in M:
        if x["slug"] == me:
            continue
        pic = picture(x["foto"], x["big"], x["wh"], "(min-width:1100px) 19vw, (min-width:700px) 31vw, 50vw", "", ' loading="lazy"')
        cards.append(f'''      <a class="more-card" href="{x["soubor"]}" data-rv>
        <span class="arch">{pic}</span>
        <span class="n">{x["num"]}</span><h3>{x["menu"]}</h3><p>{x["karta"]}</p><b>{x["od"].replace(" ", "&nbsp;")}</b>
      </a>''')
    o.append(f'''<!-- ═══════════ DALŠÍ MASÁŽE ═══════════ -->
<section style="padding-top:0">
  <div class="wide">
    <div class="rule tick" style="color:var(--c-forest)"><i></i><span>Další masáže</span><i></i></div>
    <!-- Texty karet: Reservio a homepage. Ceny ověřené v Reserviu 2. 10. 2026. -->
    <div class="more-grid more-5">
{chr(10).join(cards)}
    </div>
  </div>
</section>
''')
    # REZERVACE + KONTAKT
    o.append(f'''<!-- ═══════════ REZERVACE + KONTAKT — bez animace ═══════════ -->
<section class="a-book" id="kontakt">
  <div class="wide a-book-grid">
    <div>
      <div class="rule tick" style="color:var(--c-forest)"><i></i><span>Rezervace</span><i></i></div>
      <h2>Termín na&nbsp;pár kliknutí</h2>
      <p style="color:var(--c-ink-soft)">V&nbsp;Reserviu vyber {m["menu"]}, den a&nbsp;čas. Potvrzení přijde e-mailem.</p>
      <div class="a-cta">
        <a href="{RESV}" class="btn" target="_blank" rel="noopener">Rezervovat termín</a>
        <a href="tel:+420608654708" class="link-go">+420 608 654 708</a>
      </div>
    </div>
    <dl class="dl">
      <div><dt>Adresa</dt><dd><a href="https://www.google.com/maps/search/?api=1&amp;query=49.2095845,16.6043303" class="ul" target="_blank" rel="noopener">Skřivanova 335/5, 602&nbsp;00 Brno</a><br><span style="color:var(--c-ink-soft); font-size:.88rem">kousek od&nbsp;Lidické, pár minut od&nbsp;Lužánek</span></dd></div>
      <!-- OTEVÍRACÍ DOBA SE NESHODUJE: homepage má Po–St 12–22, Čt 9–19, Pá 9–18;
           Reservio (2. 10. 2026) Po–Pá 8–22, So–Ne 9–20. Do potvrzení od Lukáše lorem ipsum. -->
      <div><dt>Otevřeno</dt><dd>Lorem ipsum dolor sit amet</dd></div>
      <div><dt>E-mail</dt><dd><a href="mailto:info@uvolnitomasaze.cz" class="ul">info@uvolnitomasaze.cz</a></dd></div>
    </dl>
  </div>
</section>
''')
    return "\n".join(o)


def render_jsonld(m):
    url = BASE + m["slug"]
    offers = [{"@type": "Offer", "name": f'{plain(m["menu"])} {d}', "price": str(c), "priceCurrency": "CZK", "url": url}
              for d, c, _ in m["ceny"]]
    qa = []
    for f in m["faq"]:
        q, a = FAQ[f] if isinstance(f, str) else f
        if a:  # lorem ipsum do schématu nepatří
            qa.append({"@type": "Question", "name": plain(q), "acceptedAnswer": {"@type": "Answer", "text": plain(a)}})
    graph = [
        {"@type": "Service", "@id": url + "#sluzba", "name": plain(m["menu"]), "serviceType": plain(m["menu"]),
         "description": plain(m["lead"]), "url": url, "image": f'{BASE}assets/og/{m["slug"]}-og.jpg',
         "areaServed": {"@type": "City", "name": "Brno"}, "provider": {"@id": BASE + "#studio"}, "offers": offers},
        {"@type": "BreadcrumbList", "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Domů", "item": BASE},
            {"@type": "ListItem", "position": 2, "name": "Masáže", "item": BASE + "#sluzby"},
            {"@type": "ListItem", "position": 3, "name": plain(m["menu"]), "item": url}]},
    ]
    if qa:
        graph.append({"@type": "FAQPage", "mainEntity": qa})
    return json.dumps({"@context": "https://schema.org", "@graph": graph}, ensure_ascii=False)


def build(m, tpl):
    s = tpl
    url = BASE + m["slug"]
    src = f'../assets/foto/opt/2026-03-08-at-{m["foto"]}'
    pre = f'{src}-500.webp 500w, {src}-900.webp 900w' + (f', {src}-{m["big"]}.webp {m["big"]}w' if m["big"] != "900" else "")
    head = f'''<title>{H.escape(m["title"])}</title>
<meta name="description" content="{H.escape(m["desc"])}">
<link rel="canonical" href="{url}">
<meta property="og:type" content="website">
<meta property="og:locale" content="cs_CZ">
<meta property="og:site_name" content="uvolni.to — masáže">
<meta property="og:title" content="{H.escape(plain(m["h1"]))}">
<meta property="og:description" content="{H.escape(plain(m["lead"]))}">
<meta property="og:url" content="{url}">
<meta property="og:image" content="{BASE}assets/og/{m["slug"]}-og.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="../assets/logo/ODVOZENO-favicon.svg" type="image/svg+xml">
<link rel="preload" as="image" type="image/webp" fetchpriority="high"
      imagesrcset="{pre}"
      imagesizes="100vw">
'''
    s = re.sub(r"<title>.*?(?=<link rel=\"preload\" as=\"font\")", lambda _: head, s, flags=re.S)
    s = s.replace("<!--MAIN-->", render_main(m))
    s = s.replace("<!--JSONLD-->", render_jsonld(m))
    drop = "\n".join(
        (f'          <a href="#" aria-current="page">{x["menu"]} <small>{x["od"]}</small></a>' if x["slug"] == m["slug"]
         else f'          <a href="{x["soubor"]}">{x["menu"]} <small>{x["od"]}</small></a>') for x in M)
    panel = "\n".join(
        (f'        <a href="#" class="hd-sub" aria-current="page">{x["menu"]}</a>' if x["slug"] == m["slug"]
         else f'        <a href="{x["soubor"]}" class="hd-sub">{x["menu"]}</a>') for x in M)
    foot = "\n".join(
        (f'          <li><a href="#" class="ul" aria-current="page">{x["menu"]}</a></li>' if x["slug"] == m["slug"]
         else f'          <li><a href="{x["soubor"]}" class="ul">{x["menu"]}</a></li>') for x in M)
    return s.replace("<!--DROP-->", drop).replace("<!--PANEL-->", panel).replace("<!--FOOT-->", foot)


ROOT = DIR.parent


def do_korene(s):
    """Verze pro kořen webu: cesty bez ../, sdílené CSS/JS v assets/, bez pruhu návrhu.
    Odkazy mezi podstránkami zůstávají s .html — fungují lokálně i na Apache;
    přípona se dá schovat až .htaccess přepisem při nasazení."""
    s = s.replace('href="spolecne.css"', 'href="assets/css/podstranka.css"')
    s = s.replace('src="spolecne.js"', 'src="assets/js/podstranka.js"')
    s = s.replace("../", "")
    s = re.sub(r'\n<p class="navrh-pruh">.*?</p>\n', "\n", s)
    return s


if __name__ == "__main__":
    tpl = TEMPLATE.read_text(encoding="utf-8")
    for m in M:
        out = build(m, tpl)
        (DIR / m["soubor"]).write_text(out, encoding="utf-8")
        (ROOT / m["soubor"]).write_text(do_korene(out), encoding="utf-8")
        print("✓", m["soubor"], "(návrhy + kořen webu)")
    # sdílené CSS/JS do assets/ — fonty jsou z assets/css/ o úroveň výš
    css = (DIR / "spolecne.css").read_text(encoding="utf-8").replace("url('../assets/fonts/", "url('../fonts/")
    (ROOT / "assets/css/podstranka.css").write_text(css, encoding="utf-8")
    (ROOT / "assets/js/podstranka.js").write_text((DIR / "spolecne.js").read_text(encoding="utf-8"), encoding="utf-8")
    print("✓ assets/css/podstranka.css, assets/js/podstranka.js")
