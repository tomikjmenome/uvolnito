/* Formulář dárkového poukazu: ceny z assets/data/poukazy-cenik.json, živý náhled,
   termíny podle způsobu doručení a odeslání bez přenačtení stránky.
   Bez JS formulář funguje dál (klasický POST na api/poukaz.php), jen bez náhledu. */
(function () {
  var form = document.getElementById('pForm');
  if (!form || !window.fetch) return;
  var $ = function (id) { return document.getElementById(id); };
  var cenik = null;

  var kc = function (n) { return n.toLocaleString('cs-CZ').replace(/\s/g, ' ') + ' Kč'; };
  var ymd = function (d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); };
  var posun = function (dny) { var d = new Date(); d.setDate(d.getDate() + dny); return d; };
  var datumCz = function (d) { return d.getDate() + '. ' + (d.getMonth() + 1) + '. ' + d.getFullYear(); };
  var hodnota = function (name) { var el = form.querySelector('input[name="' + name + '"]:checked'); return el ? el.value : ''; };

  $('pTs').value = Date.now();

  fetch('assets/data/poukazy-cenik.json', { cache: 'no-cache' })
    .then(function (r) { if (!r.ok) throw 0; return r.json(); })
    .then(function (c) { cenik = c; start(); })
    .catch(function () { /* bez ceníku zůstane formulář v režimu bez JS — server spočítá vše sám */ });

  function start() {
    form.classList.add('p-js');
    // doručení poštou se zapíná v ceníku (posta_aktivni) — do té doby jen vidět, ne vybrat
    var posta = form.querySelector('input[name="doruceni"][value="posta"]');
    if (!cenik.posta_aktivni && posta) { posta.disabled = true; posta.checked = false; form.querySelector('input[name="doruceni"][value="email"]').checked = true; }
    document.documentElement.classList.add('js-on');
    form.addEventListener('change', obnov);
    form.addEventListener('input', function (e) {
      if (e.target.name === 'vzkaz') hlidejVzkaz(e.target);
      if (e.target.closest('.p-pole')) zrusChybu(e.target.name);
      obnovNahled();
    });
    form.addEventListener('submit', odeslat);
    // v náhledu platnost od dneška; skutečná se počítá ode dne vystavení (připsání platby)
    var platnost = new Date(); platnost.setMonth(platnost.getMonth() + cenik.platnost_mesicu);
    $('nPlatnost').textContent = datumCz(platnost);
    obnov();
  }

  function masaz() { var id = hodnota('masaz'); return cenik.masaze.filter(function (m) { return m.id === id; })[0]; }

  /* délky se staví podle vybrané masáže; vybraná délka se drží, pokud existuje */
  function postavDelky() {
    var m = masaz(), box = $('pDelky'), puvodni = hodnota('delka');
    var klice = Object.keys(m.delky).sort(function (a, b) { return a - b; });
    if (box.dataset.pro === m.id) return;
    box.dataset.pro = m.id;
    box.innerHTML = klice.map(function (d) {
      return '<label><input type="radio" name="delka" value="' + d + '"><span>' + d + ' min<em>' + kc(m.delky[d]) + '</em></span></label>';
    }).join('');
    var vyber = klice.indexOf(puvodni) >= 0 ? puvodni : klice[0];
    box.querySelector('input[value="' + vyber + '"]').checked = true;
    box.parentNode.toggleAttribute('data-jedna', klice.length === 1);
  }

  function nastavTermin() {
    var posta = hodnota('doruceni') === 'posta', el = $('pTermin');
    var min = posun(posta ? 7 : 1), max = posun(180);
    el.min = ymd(min); el.max = ymd(max);
    if (el.value && (el.value < el.min || el.value > el.max)) el.value = '';
    $('pDatumPozn').textContent = posta
      ? 'Nejdřív ' + datumCz(min) + ' — počítám s časem na převod, odeslání a doručení. Česká pošta přesný den nezaručuje, proto odesílám s rezervou.'
      : 'Nejdřív ' + datumCz(min) + '. Když platba dorazí až po vybraném dni, pošlu poukaz hned po jejím připsání.';
  }

  function obnov() {
    form.dataset.typ = hodnota('typ');
    form.dataset.doruceni = hodnota('doruceni');
    form.dataset.termin = hodnota('termin_typ');
    if (form.dataset.typ === 'masaz') postavDelky();
    nastavTermin();
    $('pTermin').required = form.dataset.termin === 'datum';
    form.querySelectorAll('[data-pro-doruceni="posta"] input').forEach(function (i) { i.required = form.dataset.doruceni === 'posta'; });
    obnovNahled();
  }

  function cena() {
    if (hodnota('typ') === 'hodnota') { var h = parseInt(hodnota('hodnota'), 10); return { nazev: 'Poukaz na ' + kc(h), cena: h }; }
    var m = masaz(), d = hodnota('delka');
    return { nazev: m.nazev + ', ' + d + ' min', cena: m.delky[d] || 0 };
  }

  function obnovNahled() {
    if (!cenik) return;
    var c = cena(), posta = hodnota('doruceni') === 'posta', celkem = c.cena + (posta ? cenik.postovne : 0);
    $('sPoukaz').textContent = c.nazev;
    $('sCena').textContent = kc(c.cena);
    $('sPostaRadek').hidden = !posta;
    $('sCelkem').textContent = kc(celkem);
    $('pOdeslat').textContent = 'Objednat poukaz za ' + kc(celkem);

    var hodn = hodnota('typ') === 'hodnota', f = hodn ? cenik.hodnota_foto : masaz();
    var src = 'assets/foto/opt/' + f.foto + '-900.webp';
    var foto = $('nFoto');
    if (foto.getAttribute('src') !== src) { foto.style.opacity = 0; foto.onload = function () { foto.style.opacity = 1; }; foto.src = src; }
    foto.style.objectPosition = 'center ' + Math.round(f.pozice * 100) + '%';
    $('nNazev').textContent = hodn ? kc(parseInt(hodnota('hodnota'), 10)) : f.nazev;
    $('nSub').textContent = hodn ? 'na jakoukoli masáž ve studiu' : hodnota('delka') + ' minut';
    var pro = form.pro.value.trim(), od = form.od.value.trim(), vzkaz = form.vzkaz.value.trim();
    $('nPro').hidden = !pro; $('nPro').textContent = 'Pro ' + pro;
    $('nOd').hidden = !od; $('nOd').textContent = 'Od ' + od;
    $('nVzkaz').hidden = !vzkaz; $('nVzkaz').textContent = '„' + vzkaz + '“';
  }

  function hlidejVzkaz(t) {
    var radky = t.value.split('\n');
    if (radky.length > 3) t.value = radky.slice(0, 3).join('\n');
    $('pPocet').textContent = t.value.length + ' / 160';
  }

  /* ═══ Chyby ═══ */
  var NAZVY = { typ: 'Poukaz', masaz: 'Masáž', delka: 'Délka', hodnota: 'Hodnota', pro: 'Pro koho', od: 'Od koho', vzkaz: 'Vzkaz',
    doruceni: 'Doručení', termin: 'Datum', adr_jmeno: 'Jméno na adrese', adr_ulice: 'Ulice', adr_mesto: 'Město', adr_psc: 'PSČ',
    jmeno: 'Jméno', email: 'E-mail', telefon: 'Telefon', platba: 'Platba', souhlas: 'Souhlas' };

  function zrusChybu(name) {
    var e = $('err-' + name); if (e) e.textContent = '';
    var el = form.elements[name]; if (el && el.removeAttribute) el.removeAttribute('aria-invalid');
  }

  function ukazChyby(chyby) {
    form.querySelectorAll('.p-err').forEach(function (e) { e.textContent = ''; });
    form.querySelectorAll('[aria-invalid]').forEach(function (e) { e.removeAttribute('aria-invalid'); });
    var box = $('pChyby'), li = [];
    Object.keys(chyby).forEach(function (k) {
      var e = $('err-' + k), el = form.elements[k];
      if (e) e.textContent = chyby[k];
      if (el && el.setAttribute) { el.setAttribute('aria-invalid', 'true'); if (e) el.setAttribute('aria-describedby', 'err-' + k); }
      li.push('<li>' + (k === '_' ? '' : '<a href="#" data-pole="' + k + '">' + (NAZVY[k] || k) + '</a>: ') + chyby[k] + '</li>');
    });
    box.innerHTML = '<strong>Ještě je potřeba opravit:</strong><ul>' + li.join('') + '</ul>';
    box.hidden = false;
    box.querySelectorAll('a[data-pole]').forEach(function (a) {
      a.addEventListener('click', function (ev) {
        ev.preventDefault();
        var el = form.elements[a.dataset.pole];
        (el && el.focus ? el : (el && el[0]) || box).focus();
      });
    });
    box.scrollIntoView({ behavior: 'smooth', block: 'start' });
    box.focus({ preventScroll: true });
  }

  /* kontrola v prohlížeči — ušetří zbytečné odeslání; server stejně kontroluje znovu */
  function zkontroluj() {
    var ch = {};
    if (!form.jmeno.value.trim()) ch.jmeno = 'Vyplň jméno a příjmení.';
    if (!form.email.checkValidity() || !form.email.value.trim()) ch.email = 'Zkontroluj e-mail, na něj přijdou platební údaje.';
    if (form.dataset.termin === 'datum' && !form.termin.value) ch.termin = 'Vyber datum doručení.';
    if (form.dataset.doruceni === 'posta') {
      if (!form.adr_jmeno.value.trim()) ch.adr_jmeno = 'Komu poukaz poslat?';
      if (!form.adr_ulice.value.trim()) ch.adr_ulice = 'Vyplň ulici a číslo domu.';
      if (!form.adr_mesto.value.trim()) ch.adr_mesto = 'Vyplň město.';
      if (!/^\d{3} ?\d{2}$/.test(form.adr_psc.value.trim())) ch.adr_psc = 'PSČ má 5 číslic.';
    }
    if (!form.souhlas.checked) ch.souhlas = 'Bez souhlasu s podmínkami nejde objednávku dokončit.';
    return ch;
  }

  function odeslat(ev) {
    ev.preventDefault();
    var ch = zkontroluj();
    if (Object.keys(ch).length) return ukazChyby(ch);
    var btn = $('pOdeslat'), puvodni = btn.textContent;
    btn.disabled = true; btn.textContent = 'Odesílám…';
    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } })
      .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
      .then(function (x) {
        if (!x.ok || !x.d.ok) throw x.d.chyby || { _: 'Něco se pokazilo. Zkus to prosím znovu.' };
        hotovo(x.d);
      })
      .catch(function (chyby) {
        btn.disabled = false; btn.textContent = puvodni;
        ukazChyby(chyby && typeof chyby === 'object' && !(chyby instanceof Error) ? chyby
          : { _: 'Spojení se nepovedlo. Zkus to prosím znovu, nebo zavolej na +420 608 654 708.' });
      });
  }

  function hotovo(d) {
    $('hCislo').textContent = d.cislo;
    $('hEmail').textContent = d.email;
    $('hTermin').textContent = d.termin;
    $('hCelkem').textContent = d.celkem_text;
    $('hUcet').textContent = d.ucet;
    $('hVs').textContent = d.vs;
    $('hZprava').textContent = 'Poukaz ' + d.cislo;
    $('hQr').src = d.qr;
    form.hidden = true;
    document.querySelector('.p-nahled-box').hidden = true;
    var h = $('pHotovo');
    h.hidden = false;
    h.scrollIntoView({ behavior: 'smooth', block: 'start' });
    h.focus({ preventScroll: true });
  }
})();
