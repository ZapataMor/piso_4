/* ============================================================
   PISO CUATRO — controlador de la intro de ascensor
   Sincronizado con assets/elevator.mp3 (dings ~6s, 9s, 14.1s)
   ============================================================ */
(function(){
  "use strict";
  const root  = document.getElementById("intro");
  if(!root) return;

  const audio = document.getElementById("ele-audio");
  const btn   = root.querySelector(".btn4");
  const digit = root.querySelector(".digit");
  const ghost = root.querySelector(".ghost");

  const lockScroll   = ()=>{ document.documentElement.style.overflow="hidden"; document.body.style.overflow="hidden"; };
  const unlockScroll = ()=>{ document.documentElement.style.overflow=""; document.body.style.overflow=""; };
  lockScroll();

  function setFloor(n){
    digit.textContent = n;
    if(ghost) ghost.textContent = "8";
    digit.style.opacity = ".2";
    requestAnimationFrame(()=>{ digit.style.transition="opacity .18s ease"; digit.style.opacity="1"; });
  }

  let started=false, finished=false, raf=0, startT=0, idx=0;

  function start(){            // piso 1, ascenso
    setFloor(1);
    root.classList.add("moving","lit");
  }
  function arrival(){          // llega al piso 4: el panel se va y el logo aparece suavemente
    setFloor(4);
    root.classList.remove("moving");
    root.classList.add("arrived");
    setTimeout(()=>root.classList.add("brand-in"), 400);
  }
  function openDoors(){
    root.classList.add("brand-out");
    setTimeout(()=>{
      root.classList.add("opening");
      window.dispatchEvent(new Event("piso:reveal"));
      setTimeout(finish, 1300);
    }, 400);
  }

  const steps = [
    { t:0.0,  fn:start },
    { t:1.2,  fn:()=>setFloor(2) },
    { t:2.0,  fn:()=>setFloor(3) },
    { t:3.0,  fn:arrival },
    { t:4.2,  fn:openDoors }
  ];

  function loop(now){
    if(finished) return;
    const elapsed = (now - startT)/1000;
    while(idx < steps.length && elapsed >= steps[idx].t){ steps[idx].fn(); idx++; }
    if(idx < steps.length) raf = requestAnimationFrame(loop);
  }

  function press(){
    if(started) return; started=true;
    root.classList.add("pressed");
    btn.classList.add("lit");
    // desbloquea el audio con el gesto del usuario
    if(audio){ audio.currentTime=0; audio.volume=1; audio.play().catch(()=>{}); }
    startT = performance.now();
    raf = requestAnimationFrame(loop);
    // seguridad: si la secuencia ya iniciada se cuelga, no dejar la cortina puesta
    setTimeout(()=>{ if(!finished) finish(); }, 26000);
  }

  function finish(){
    if(finished) return; finished=true;
    cancelAnimationFrame(raf);
    if(audio){ try{ audio.pause(); }catch(e){} }
    window.dispatchEvent(new Event("piso:reveal"));   // por si acaso
    unlockScroll();
    root.classList.add("opening");
    setTimeout(()=>root.classList.add("gone"), 900);
  }

  function skip(){
    if(finished) return;
    cancelAnimationFrame(raf);
    if(audio){ try{ audio.pause(); }catch(e){} }
    root.classList.add("brand-out");
    finish();
  }

  // aparición del panel
  setTimeout(()=>root.classList.add("panel-in"), 450);

  btn.addEventListener("click", press);
  root.querySelector(".skip").addEventListener("click", skip);
  // La animación SOLO inicia cuando el cliente pulsa el botón. No hay arranque automático.
})();
