/* ═══════════════════════════════════════════════════════════════════════
   SOUHLAS S COOKIES A VLOŽENÝM OBSAHEM
   ───────────────────────────────────────────────────────────────────────
   Proč to vůbec je: web sám neukládá nic. Ukládají ale rámy třetích stran
   — rezervační kalendář Reservio a mapa Google. Podle § 89 odst. 3 zákona
   č. 127/2005 Sb. (od 1. 1. 2022) se k tomu musí dát souhlas PŘEDEM, ne
   „návštěvou stránky souhlasíte". Proto se rámy nevkládají do stránky
   dřív, než souhlas je — bez něj neodejde na cizí server ani jeden
   požadavek, tedy ani IP adresa návštěvníka.

   Jak přidat další kategorii (analytika, remarketing…):
   1) přibude položka v KATEGORIE níž,
   2) zvedne se VERZE, aby se lidí zeptalo znovu,
   3) v HTML se prvek označí data-cc-kat="<id nové kategorie>".
   Nic jiného se nemění.
   ═══════════════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  var KLIC  = 'uvolnito-souhlas';
  /* Verze se zvedá při KAŽDÉ změně seznamu kategorií nebo příjemců údajů.
     Uložený souhlas se starší verzí se zahodí a lišta se ukáže znovu —
     souhlas se dává k rozsahu, který byl v tu chvíli popsaný. */
  var VERZE = 1;

  var KATEGORIE = [
    {
      id: 'nezbytne',
      nazev: 'Nezbytné',
      popis: 'Drží tvoje rozhodnutí o cookies a základní chod stránky. Bez nich by se lišta ptala pořád dokola. Nedají se vypnout a nikam se neposílají.',
      povinna: true
    },
    {
      id: 'vlozeny-obsah',
      nazev: 'Vložený obsah',
      popis: 'Rezervační kalendář Reservio a mapa Google přímo ve stránce. Oba servery si při zobrazení uloží vlastní cookies a vidí tvoji IP adresu. Bez souhlasu se místo nich ukáže odkaz do nového okna.',
      povinna: false
    }
  ];

  /* ── Uložený stav ────────────────────────────────────────────────────── */

  /* localStorage může být zakázané (přísné nastavení soukromí, Safari
     v soukromém režimu u starších verzí). Čtení i zápis proto vždy v try —
     když úložiště nejde, lišta se zeptá při každé návštěvě, ale web běží. */
  function nacti() {
    try {
      var s = JSON.parse(localStorage.getItem(KLIC));
      if (!s || s.v !== VERZE || !s.kat) return null;
      return s;
    } catch (e) { return null; }
  }
  function uloz(kat) {
    var s = { v: VERZE, kdy: new Date().toISOString(), kat: kat };
    try { localStorage.setItem(KLIC, JSON.stringify(s)); } catch (e) {}
    return s;
  }

  var stav = nacti();

  function povoleno(id) {
    var k = KATEGORIE.filter(function (x) { return x.id === id; })[0];
    if (k && k.povinna) return true;
    return !!(stav && stav.kat[id]);
  }

  /* ── Vložený obsah ───────────────────────────────────────────────────── */

  /* Prvek s data-cc-src je rám, který se smí naplnit teprve se souhlasem.
     Do té doby na jeho místě sedí zástupná plocha s vysvětlením a tlačítkem.
     Texty jsou v HTML (data-cc-popis, data-cc-tlacitko), ne tady — čeština
     patří do dokumentu, ne do skriptu. */
  function ramy() {
    return Array.prototype.slice.call(document.querySelectorAll('[data-cc-src]'));
  }

  function nasad(fr) {
    if (fr.dataset.ccNasazeno) return;
    /* Reservio pouští vložení jen z https původu. Otevřené ze souboru na disku
       nebo z http by zůstal prázdný rám — v tom případě se nevkládá vůbec
       a zůstane viditelný záložní odkaz do nového okna. */
    if (fr.hasAttribute('data-cc-jen-https') && location.protocol !== 'https:') return;
    fr.dataset.ccNasazeno = '1';
    fr.hidden = false;
    fr.src = fr.dataset.ccSrc;
    uklidZastup(fr);
    var zaloha = fr.dataset.ccZaloha && document.getElementById(fr.dataset.ccZaloha);
    if (zaloha) zaloha.hidden = true;
  }

  function uklidZastup(fr) {
    var z = fr.parentNode && fr.parentNode.querySelector('.cc-zastup');
    if (z) z.parentNode.removeChild(z);
  }

  function zastup(fr) {
    if (fr.dataset.ccNasazeno) return;
    if (fr.hasAttribute('data-cc-jen-https') && location.protocol !== 'https:') return;
    if (fr.parentNode.querySelector('.cc-zastup')) return;

    /* Záložní blok (odkaz do nového okna pro případ, že rám vůbec nejde) se
       schová — zástupná plocha říká totéž a nabízí navíc zobrazení na místě.
       Když se sem kód nedostane (vypnutý JS, jiný než https původ), záloha
       zůstane viditelná, což je přesně ta situace, pro kterou existuje. */
    var zal = fr.dataset.ccZaloha && document.getElementById(fr.dataset.ccZaloha);
    if (zal) zal.hidden = true;

    var box = document.createElement('div');
    box.className = 'cc-zastup';

    var stitek = document.createElement('p');
    stitek.className = 'cc-label';
    stitek.textContent = fr.dataset.ccNazev || 'Vložený obsah';

    var popis = document.createElement('p');
    popis.textContent = fr.dataset.ccPopis || '';

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'cc-btn';
    btn.textContent = fr.dataset.ccTlacitko || 'Zobrazit';
    /* Jednorázové zobrazení: načte jen tenhle rám a nikam se neukládá.
       Kdo chce mít mapu i kalendář rovnou pokaždé, zapne si kategorii
       v nastavení lišty. */
    btn.addEventListener('click', function () { nasad(fr); });

    box.appendChild(stitek);
    if (popis.textContent) box.appendChild(popis);
    box.appendChild(btn);

    /* Volitelná úniková cesta: totéž v novém okně, bez vkládání do stránky.
       U rezervace je to důležité — nikdo nesmí přijít o možnost objednat se
       jen proto, že nechce cizí cookies. */
    if (fr.dataset.ccOdkaz) {
      var p = document.createElement('p');
      var a = document.createElement('a');
      a.href = fr.dataset.ccOdkaz;
      a.target = '_blank';
      a.rel = 'noopener';
      a.className = 'cc-odkaz';
      a.textContent = fr.dataset.ccOdkazText || 'Otevřít v novém okně';
      p.appendChild(a);
      box.appendChild(p);
    }
    fr.parentNode.insertBefore(box, fr);
    fr.hidden = true;
  }

  function srovnejRamy() {
    ramy().forEach(function (fr) {
      if (povoleno(fr.dataset.ccKat || 'vlozeny-obsah')) nasad(fr);
      else zastup(fr);
    });
  }

  /* ── Lišta ───────────────────────────────────────────────────────────── */

  var listaEl = null;

  function zavri() {
    if (!listaEl) return;
    listaEl.parentNode.removeChild(listaEl);
    listaEl = null;
  }

  function rozhodni(kat) {
    stav = uloz(kat);
    zavri();
    srovnejRamy();
    document.dispatchEvent(new CustomEvent('souhlas:zmena', { detail: stav }));
  }

  function vse(hodnota) {
    var k = {};
    KATEGORIE.forEach(function (x) { k[x.id] = x.povinna ? true : hodnota; });
    return k;
  }

  function lista(rovnouNastaveni) {
    if (listaEl) {
      if (rovnouNastaveni) listaEl.querySelector('.cc-panel').hidden = false;
      return;
    }

    var el = document.createElement('aside');
    el.className = 'cc';
    el.setAttribute('role', 'dialog');
    el.setAttribute('aria-labelledby', 'cc-nadpis');
    /* Ne aria-modal a ne past na fokus: lišta nesmí uvěznit čtečku ani
       klávesnici u obsahu, který jde legitimně ignorovat. Blokovat čtení
       webu kvůli souhlasu je i právně vratká cesta. */

    var telo = document.createElement('div');
    telo.className = 'cc-body';
    telo.innerHTML =
      '<p class="cc-label">Soukromí</p>' +
      '<h2 id="cc-nadpis">Než se pustíme dál</h2>' +
      '<p>Sám o sobě tenhle web nic nesleduje — žádná analytika, žádný ' +
      'remarketing, písma běží z vlastního serveru. Cookies si ukládá jen ' +
      'rezervační kalendář&nbsp;Reservio a&nbsp;mapa&nbsp;Google, když je ' +
      'zobrazím ve stránce. Do tvého rozhodnutí je nenačítám. ' +
      '<a href="pravni-informace.html#dok-cookies">Podrobnosti</a></p>';

    var panel = document.createElement('div');
    panel.className = 'cc-panel';
    panel.hidden = !rovnouNastaveni;

    KATEGORIE.forEach(function (k) {
      var radek = document.createElement('div');
      radek.className = 'cc-radek';

      var jmeno = document.createElement('b');
      jmeno.textContent = k.nazev;

      var sw = document.createElement('label');
      sw.className = 'cc-sw';
      var vstup = document.createElement('input');
      vstup.type = 'checkbox';
      vstup.dataset.kat = k.id;
      vstup.checked = k.povinna || povoleno(k.id);
      vstup.disabled = !!k.povinna;
      vstup.setAttribute('aria-label', k.nazev);
      sw.appendChild(vstup);
      sw.appendChild(document.createElement('i'));

      var popis = document.createElement('small');
      popis.textContent = k.popis;

      radek.appendChild(jmeno);
      radek.appendChild(sw);
      radek.appendChild(popis);
      panel.appendChild(radek);
    });

    var akce = document.createElement('div');
    akce.className = 'cc-akce';

    var ano = document.createElement('button');
    ano.type = 'button';
    ano.className = 'cc-btn';
    ano.textContent = 'Přijmout vše';
    ano.addEventListener('click', function () { rozhodni(vse(true)); });

    var ne = document.createElement('button');
    ne.type = 'button';
    ne.className = 'cc-btn cc-btn-2';
    ne.textContent = 'Jen nezbytné';
    ne.addEventListener('click', function () { rozhodni(vse(false)); });

    var vic = document.createElement('button');
    vic.type = 'button';
    vic.className = 'cc-vic';
    /* Když lišta rovnou otevírá nastavení (odkaz z patičky, tlačítko na stránce
       Cookies), musí popisek odpovídat tomu, co tlačítko udělá — tedy uložit. */
    vic.textContent = panel.hidden ? 'Nastavení' : 'Uložit výběr';
    vic.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
    vic.addEventListener('click', function () {
      if (panel.hidden) {
        panel.hidden = false;
        vic.setAttribute('aria-expanded', 'true');
        vic.textContent = 'Uložit výběr';
        /* Ve chvíli, kdy je vidět výběr, musí hlavní tlačítko odpovídat
           tomu, co je zaškrtnuté — jinak by přepínače byly na okrasu. */
        ano.textContent = 'Přijmout vše';
      } else {
        var k = {};
        KATEGORIE.forEach(function (x) {
          var i = panel.querySelector('input[data-kat="' + x.id + '"]');
          k[x.id] = x.povinna ? true : !!(i && i.checked);
        });
        rozhodni(k);
      }
    });

    akce.appendChild(ano);
    akce.appendChild(ne);
    akce.appendChild(vic);

    telo.appendChild(panel);
    telo.appendChild(akce);
    el.appendChild(telo);
    document.body.appendChild(el);
    listaEl = el;

    /* Fokus na nadpis, ne na tlačítko: čtečka přečte, o co jde,
       a nikoho to nevystřelí z místa, kde právě četl. */
    var h = el.querySelector('h2');
    h.setAttribute('tabindex', '-1');
    if (rovnouNastaveni) h.focus();
  }

  /* ── Veřejné rozhraní ────────────────────────────────────────────────── */

  window.souhlas = {
    /* Otevře lištu rovnou v nastavení — na tohle míří odkaz „Nastavení
       cookies" v patičce a tlačítko na stránce Cookies. */
    otevri: function () { lista(true); },
    stav: function () { return stav ? JSON.parse(JSON.stringify(stav)) : null; },
    povoleno: povoleno,
    /* Zapomene rozhodnutí a zeptá se znovu. */
    zrus: function () {
      try { localStorage.removeItem(KLIC); } catch (e) {}
      stav = null;
      zavri();
      srovnejRamy();
      lista(true);
    }
  };

  function start() {
    srovnejRamy();
    if (!stav) lista(false);
    /* Odkazy kdekoli ve stránce: <a href="#" data-cc-otevrit> */
    document.addEventListener('click', function (e) {
      var t = e.target.closest && e.target.closest('[data-cc-otevrit]');
      if (!t) return;
      e.preventDefault();
      lista(true);
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
