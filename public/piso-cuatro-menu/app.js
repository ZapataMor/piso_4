/* ============================================================
   PISO CUATRO — render + coreografía de scroll cinematográfica
   ============================================================ */
(function(){
  "use strict";
  const DATA = window.PISO_MENU || [];
  const esc = s => String(s).replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));

  /* ---------- plantillas ---------- */
  function dishHTML(item, i){
    const cls = ["dish"];
    if (item.feature) cls.push("dish--feature");
    if (!item.price)  cls.push("dish--np");
    return `<li class="${cls.join(' ')}" style="--i:${i}">
      <div class="dish__main">
        <div class="dish__name">${esc(item.name)}</div>
        ${item.desc ? `<div class="dish__desc">${esc(item.desc)}</div>` : ``}
      </div>
      ${item.price ? `<div class="dish__price">${esc(item.price)}</div>` : ``}
    </li>`;
  }

  function groupHTML(g, base){
    const items = g.items.map((it,k)=>dishHTML(it, base+k)).join("");
    return `<div class="group-block">
      <h3 class="menu__group-title">${esc(g.title)}${g.note?`<span class="menu__group-note">${esc(g.note)}</span>`:``}</h3>
      <ul class="menu__list">${items}</ul>
    </div>`;
  }

  function chapterHTML(c, idx){
    let listInner = "";
    let i = 0;
    if (c.items){ listInner += `<ul class="menu__list">${c.items.map(it=>dishHTML(it,i++)).join("")}</ul>`; }
    if (c.groups){
      const colClass = (c.groups.length>2 && !c.items) ? " menu--cols" : "";
      listInner += `<div class="menu__groups${colClass}">${c.groups.map(g=>{ const h=groupHTML(g,i); i+=g.items.length; return h; }).join("")}</div>`;
    }

    const shortImg = (c.id==="fuertes"); // recorte ancho
    const figCls = "chapter__figure";

    return `<section class="chapter${shortImg?' chapter--shortimg':''}" id="${c.id}" data-bg="${c.bg}" data-screen-label="${esc(c.name)}">
      <div class="chapter__intro">
        <div class="chapter__pin">
          <div class="inner">
            <span class="chapter__kicker kicker">${esc(c.kicker)}</span>
            <h2 class="chapter__title serif metal">${esc(c.name)}</h2>
            <p class="chapter__sub">${esc(c.sub)}</p>
          </div>
        </div>
      </div>
      <div class="chapter__content">
        <div class="${figCls}">
          <figure class="chapter__photo">
            <img src="${c.photo}" alt="${esc(c.name)} — Piso Cuatro" loading="lazy">
            <span class="frame"></span>
          </figure>
          <figcaption class="chapter__figcap">Piso Cuatro · ${esc(c.name)}</figcaption>
        </div>
        <div class="chapter__menu">
          <div class="menu__head">
            <span class="kicker">Carta</span>
            <h2 class="serif">${esc(c.name)}</h2>
          </div>
          ${listInner}
        </div>
      </div>
    </section>`;
  }

  /* ---------- montar ---------- */
  const root = document.getElementById("chapters");
  if (root) root.innerHTML = DATA.map(chapterHTML).join("");

  function scrollToHash(hash, behavior){
    if (!hash || hash === "#") return false;
    try {
      const target = document.getElementById(decodeURIComponent(hash.slice(1)));
      if (!target) return false;
      const destination = target.classList.contains("chapter")
        ? target.querySelector(".chapter__content") || target
        : target;
      const top = destination.getBoundingClientRect().top + window.scrollY - 92;
      if (behavior === "auto") window.scrollTo(0, Math.max(0, top));
      else window.scrollTo({ top: Math.max(0, top), behavior });
      return true;
    } catch (e) {}
    return false;
  }

  if (window.location.hash) requestAnimationFrame(() => scrollToHash(window.location.hash, "auto"));
  document.querySelectorAll('a[href^="#"]').forEach(a=>{
    a.addEventListener("click", e=>{
      const hash = a.getAttribute("href");
      if (scrollToHash(hash, "smooth")) {
        e.preventDefault();
        history.pushState(null, "", hash);
      }
    });
  });

  /* ---------- reveal de platos ---------- */
  const io = new IntersectionObserver((entries)=>{
    entries.forEach(e=>{ if(e.isIntersecting){ e.target.classList.add("is-in"); io.unobserve(e.target); }});
  }, { threshold:.18, rootMargin:"0px 0px -8% 0px" });
  document.querySelectorAll(".dish").forEach(d=>io.observe(d));

  /* ---------- coreografía de scroll ---------- */
  const head   = document.querySelector(".head");
  const bg     = document.getElementById("bg");
  const intros = [...document.querySelectorAll(".chapter")].map(ch=>({
    ch,
    intro: ch.querySelector(".chapter__intro"),
    pin:   ch.querySelector(".chapter__pin"),
    inner: ch.querySelector(".chapter__pin .inner"),
    img:   ch.querySelector(".chapter__photo img"),
    fig:   ch.querySelector(".chapter__figure"),
    bgType: ch.dataset.bg
  }));

  const clamp = (v,a,b)=>Math.max(a,Math.min(b,v));
  const lerp  = (a,b,t)=>a+(b-a)*t;
  // mapea x de [a,b] a [0,1]
  const seg = (x,a,b)=> clamp((x-a)/(b-a),0,1);

  let curBg = "smoke";
  function setBg(t){ if(t===curBg) return; curBg=t; bg.classList.toggle("is-smoke", t==="smoke"); bg.classList.toggle("is-bubbles", t==="bubbles"); }

  const veilEl = document.getElementById("veil");
  const smooth = t => t*t*(3-2*t); // suavizado

  let ticking=false;
  function frame(){
    ticking=false;
    const vh = window.innerHeight;
    const y  = window.scrollY;

    head.classList.toggle("scrolled", y>60);

    let active=null, activeArea=-1;
    let darkness=0; // oscurecimiento global del fondo

    for (const o of intros){
      const r = o.intro.getBoundingClientRect();
      const span = o.intro.offsetHeight - vh;
      const p = span>0 ? clamp(-r.top/span, 0, 1) : (r.top<=0?1:0);

      // zoom cinematográfico del título — meseta amplia para que cada título
      // se vea bien aunque se haga scroll rápido (sobre todo en móvil)
      const op    = seg(p,0,.07) * (1 - seg(p,.86,.98));
      const scale = lerp(.82, 1.0, seg(p,0,.10)) + seg(p,.10,.92)*0.7;
      const blur  = lerp(6,0,seg(p,0,.10)) + seg(p,.82,.96)*11;
      o.inner.style.setProperty("--ttl-op", op.toFixed(3));
      o.inner.style.setProperty("--ttl-scale", scale.toFixed(3));
      o.inner.style.setProperty("--ttl-blur", blur.toFixed(2)+"px");

      // contribución al oscurecimiento: ventana más estrecha para no solapar entre capítulos
      const darkIn  = smooth(seg(p,0,.14));
      const darkOut = 1 - smooth(seg(p,.68,.88));
      const b = Math.min(darkIn, darkOut);
      if (b>darkness) darkness = b;

      // parallax de la foto
      if (o.img){
        const fr = o.fig.getBoundingClientRect();
        const prog = clamp((vh - fr.top)/(vh+fr.height), 0, 1);
        o.img.style.setProperty("--par", (lerp(-26,26,prog)).toFixed(1)+"px");
      }

      // capítulo activo para el fondo (el que cruza el centro)
      const cr = o.ch.getBoundingClientRect();
      const top = Math.max(cr.top, 0), bot = Math.min(cr.bottom, vh);
      const vis = Math.max(0, bot-top);
      if (vis>activeArea){ activeArea=vis; active=o; }
    }
    veilEl.style.opacity = darkness.toFixed(3);
    if (active) setBg(active.bgType);
  }
  function onScroll(){ if(!ticking){ ticking=true; requestAnimationFrame(frame); } }
  window.addEventListener("scroll", onScroll, {passive:true});
  window.addEventListener("resize", onScroll);
  frame();

  /* ---------- gatillo de entrada del hero (al abrir las puertas del ascensor) ---------- */
  const reveal = ()=> document.body.classList.add("ready");
  if (document.getElementById("intro")){
    window.addEventListener("piso:reveal", reveal, { once:true });
    setTimeout(reveal, 32000);
  } else {
    requestAnimationFrame(()=>requestAnimationFrame(reveal));
  }

  /* ---------- videos: reproducción lenta, suave y robusta ---------- */
  document.querySelectorAll("#bg video").forEach(v=>{
    const rate = v.classList.contains("smoke") ? 1.0 : 0.55;
    const apply = ()=>{ try{ v.playbackRate = rate; }catch(e){} };
    apply();
    ["loadedmetadata","canplay","playing"].forEach(ev=> v.addEventListener(ev, apply));
    const play = ()=>{ const p = v.play(); if(p&&p.then) p.then(apply).catch(()=>{}); else apply(); };
    play();
    ["click","touchstart","scroll","keydown"].forEach(ev=> document.addEventListener(ev, play, {once:true, passive:true}));
  });
})();
