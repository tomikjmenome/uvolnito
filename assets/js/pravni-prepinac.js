/* ═══════════════════════════════════════════════════════════════════════
   PŘEPÍNAČ PRÁVNÍCH DOKUMENTŮ
   ───────────────────────────────────────────────────────────────────────
   Tři dokumenty leží v jedné stránce pod sebou. Bez JS je to prostě dlouhá
   stránka s kotvami — všechno čitelné, přepínač funguje jako odkazy. S JS
   je vidět vždy jen jeden dokument a přepínač mezi nimi přepíná.

   Adresa se drží aktuálním dokumentem, aby šel odkaz poslat dál. Funguje
   i hluboký odkaz na konkrétní bod (#op-storno z rezervační sekce na webu):
   otevře se dokument, kterému bod patří, a stránka na něj doroluje.
   ═══════════════════════════════════════════════════════════════════════ */
(function () {
  'use strict';

  var panely = Array.prototype.slice.call(document.querySelectorAll('.pr-dok'));
  var taby   = Array.prototype.slice.call(document.querySelectorAll('.pr-zal'));
  if (!panely.length || !taby.length) return;

  /* Kde co leží: id každé sekce i každého panelu -> id panelu.
     Díky předponám (ou-, ck-, op-) se id nemůžou srazit. */
  var domov = {};
  panely.forEach(function (p) {
    domov[p.id] = p.id;
    p.querySelectorAll('[id]').forEach(function (el) { domov[el.id] = p.id; });
  });

  function zobraz(idPanelu, posunNahoru) {
    if (!domov[idPanelu]) return false;
    idPanelu = domov[idPanelu];

    panely.forEach(function (p) { p.hidden = (p.id !== idPanelu); });
    taby.forEach(function (t) {
      var je = t.getAttribute('href') === '#' + idPanelu;
      if (je) t.setAttribute('aria-current', 'page');
      else t.removeAttribute('aria-current');
    });

    /* Při přepnutí dokumentu se čte od začátku — bez toho by člověk skončil
       uprostřed nového textu v místě, kde byl v tom starém. */
    if (posunNahoru) {
      var hl = document.querySelector('#' + idPanelu + ' .pr-dok-hlava');
      var y  = hl ? hl.getBoundingClientRect().top + window.scrollY - odsazeni() : 0;
      window.scrollTo({ top: Math.max(0, y), behavior: skok() });
    }
    return true;
  }

  /* Kdo si vypnul animace, nechce ani hladké rolování. */
  function skok() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
  }

  /* O kolik musí cíl zůstat od horní hrany: lepivý přepínač by ho jinak
     zakryl. Počítá se z živé výšky, ne z natvrdo napsaného čísla. */
  function odsazeni() {
    var z = document.querySelector('.pr-zalozky');
    return (z ? z.getBoundingClientRect().height : 0) + 16;
  }

  function dorolujNa(el) {
    /* Panel se právě odkryl, takže se pozice dá spočítat až v dalším snímku —
       do té chvíle má skrytý panel nulovou výšku a cíl by seděl jinde.
       Počítáme ručně místo scrollIntoView: to by nechalo nadpis pod lištou. */
    requestAnimationFrame(function () {
      var y = el.getBoundingClientRect().top + window.scrollY - odsazeni();
      window.scrollTo({ top: Math.max(0, y), behavior: skok() });
    });
  }

  function zHashe(hash, posun) {
    var id = (hash || '').replace(/^#/, '');
    if (!id || !domov[id]) return false;
    zobraz(id, false);
    var cil = document.getElementById(id);
    if (cil && !/pr-dok/.test(cil.className)) dorolujNa(cil);
    else if (posun) window.scrollTo({ top: 0, behavior: 'auto' });
    return true;
  }

  /* Klik kdekoli ve stránce na odkaz mířící do některého dokumentu:
     přepínač nahoře, rejstřík vlevo i křížové odkazy uvnitř textu. */
  document.addEventListener('click', function (e) {
    var a = e.target.closest && e.target.closest('a[href^="#"]');
    if (!a) return;
    var id = a.getAttribute('href').slice(1);
    if (!domov[id]) return;
    e.preventDefault();
    if (history.replaceState) history.replaceState(null, '', '#' + id);
    else location.hash = id;
    if (domov[id] === id) zobraz(id, true);   /* odkaz na celý dokument */
    else zHashe('#' + id, false);             /* odkaz na konkrétní bod */
  });

  window.addEventListener('hashchange', function () { zHashe(location.hash, true); });

  /* Vstup zvenčí: /pravni-informace.html#op-storno otevře podmínky u storna. */
  if (!zHashe(location.hash, true)) zobraz(panely[0].id, false);
})();
