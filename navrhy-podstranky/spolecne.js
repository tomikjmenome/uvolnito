/* Společné chování podstránek — návrh budoucího assets/js/podstranka.js.
   Bez knihoven: podstránky nepinují ani nescrubují, GSAP by tu byl jen zátěž.
   Všechno funguje i bez JS (menu, rozbalovací seznam i FAQ jsou <details>). */
(function () {
  var root = document.documentElement;
  var REDUCE = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ═══ Hlavička — světlý podklad po odjetí z hero ═══
     data-solid-od na <header> říká, od jaké části výšky okna se přepne;
     0 = světlá hned (stránky se světlým hero). */
  var head = document.getElementById('head');
  if (head) {
    var od = parseFloat(head.getAttribute('data-solid-od') || '0.6');
    var solid = function () { head.classList.toggle('solid', window.scrollY > window.innerHeight * od || od === 0); };
    solid();
    window.addEventListener('scroll', solid, { passive: true });
  }

  /* ═══ Mobilní menu — zámek scrollu a zavření po výběru ═══ */
  var menu = document.getElementById('hdMenu');
  if (menu) {
    menu.addEventListener('toggle', function () { root.classList.toggle('menu-open', menu.open); });
    menu.querySelectorAll('a').forEach(function (a) { a.addEventListener('click', function () { menu.open = false; }); });
    window.matchMedia('(min-width:1000px)').addEventListener('change', function (m) { if (m.matches) menu.open = false; });
  }

  /* ═══ Rozbalovací „Masáže“ — zavřít klikem mimo a Escapem ═══ */
  var drop = document.querySelector('.hd-drop');
  if (drop) {
    document.addEventListener('click', function (e) { if (drop.open && !drop.contains(e.target)) drop.open = false; });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (menu) menu.open = false;
    if (drop) drop.open = false;
  });

  /* ═══ Odkrytí sekcí — jednou, nic se nepřehrává zpátky ═══ */
  var els = document.querySelectorAll('[data-rv]');
  if (REDUCE || !('IntersectionObserver' in window)) return;
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (!en.isIntersecting) return;
      en.target.classList.add('in');
      io.unobserve(en.target);
    });
  }, { rootMargin: '0px 0px -8% 0px' });
  els.forEach(function (el) {
    /* co je při načtení vidět, se neschovává — žádné probliknutí nad ohybem.
       Třída .in musí přijít DŘÍV než .js na <html>, jinak to jednou blikne. */
    if (el.getBoundingClientRect().top < window.innerHeight) { el.classList.add('in'); return; }
    io.observe(el);
  });
  root.classList.add('js');
  /* pojistka: kdyby observer nedoběhl, obsah nesmí zůstat neviditelný */
  setTimeout(function () { els.forEach(function (el) { el.classList.add('in'); }); }, 6000);
})();
