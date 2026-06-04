<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Piso Cuatro · Restaurante & Bar</title>
    <meta name="description" content="Piso Cuatro — Alta cocina sobre la ciudad. Una experiencia gastronómica única.">

    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400..700;1,6..96,400..600&family=Manrope:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('piso-cuatro-menu/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('piso-cuatro-menu/intro.css') }}">
</head>
<body>
    <div id="intro">
        <button class="skip" type="button">Saltar ›</button>
        <div class="door left"><span class="seam"></span></div>
        <div class="door right"><span class="seam"></span></div>
        <div class="shaft"></div>
        <div class="glowbg"></div>

        <div class="stage">
            <div class="panel">
                <div class="display">
                    <span class="arrow"></span>
                    <span class="digit-wrap"><span class="ghost"></span><span class="digit">1</span></span>
                </div>
                <button class="btn4" type="button" aria-label="Subir al cuarto piso">
                    <span>Subir</span>
                    <span class="ripple"></span>
                </button>
            </div>

            <div class="brand">
                <img src="{{ asset('piso-cuatro-menu/assets/logo-white.png') }}" alt="Piso Cuatro">
                <span class="ruleline"></span>
                <span class="sublabel">Restaurante · Bar</span>
            </div>
        </div>

        <div class="hint"><span class="pulse">Pulsa para subir</span></div>
    </div>
    <audio id="ele-audio" src="{{ asset('piso-cuatro-menu/assets/elevator.mp3') }}" preload="auto"></audio>

    <div id="bg" class="is-smoke">
        <video class="layer smoke" src="{{ asset('piso-cuatro-menu/assets/humo.mp4') }}" muted loop playsinline preload="auto" autoplay></video>
        <video class="layer bubbles" src="{{ asset('piso-cuatro-menu/assets/bubbles.mp4') }}" muted loop playsinline preload="auto" autoplay></video>
    </div>
    <div id="veil"></div>

    <header class="head">
        <a class="head__logo" href="#top" aria-label="Piso Cuatro — inicio">
            <img src="{{ asset('piso-cuatro-menu/assets/logo-white.png') }}" alt="Piso Cuatro">
        </a>
        <nav class="head__nav">
            <div class="head__links">
                <a href="#entradas">Entradas</a>
                <a href="#fuertes">Platos Fuertes</a>
                <a href="#postres">Postres</a>
                <a href="#bebidas">Bebidas</a>
                <a href="#cocteles">Cócteles</a>
            </div>
            <a class="btn-reserve" href="https://wa.me/573122424234?text=Hola%20Piso%20Cuatro,%20quisiera%20reservar%20una%20mesa." target="_blank" rel="noopener">
                <span class="dot"></span> Reservar
            </a>
        </nav>
    </header>

    <main id="top">
        <section class="hero">
            <span class="hero__tag kicker">Restaurante · Bar · Rooftop</span>
            <img class="hero__logo" src="{{ asset('piso-cuatro-menu/assets/logo-white.png') }}" alt="Piso Cuatro">
            <div class="hero__rule"></div>
            <h1 class="hero__slogan serif metal">Alta cocina<br>sobre la ciudad</h1>
            <p class="hero__sub">Cocinamos ideas, servimos emociones. Una experiencia gastronómica única en el cuarto piso.</p>
            <div class="hero__cta">
                <a class="btn-reserve" href="https://wa.me/573122424234?text=Hola%20Piso%20Cuatro,%20quisiera%20reservar%20una%20mesa." target="_blank" rel="noopener">
                    <span class="dot"></span> Reservar una mesa
                </a>
                <a class="btn-ghost" href="#entradas">Explorar la carta</a>
            </div>
            <div class="hero__scroll">
                <span class="mouse"></span>
                Desliza para comenzar
            </div>
        </section>

        <div id="chapters"></div>
    </main>

    <footer class="foot" id="contacto">
        <img class="foot__logo" src="{{ asset('piso-cuatro-menu/assets/logo-white.png') }}" alt="Piso Cuatro">
        <div class="foot__line"></div>
        <p class="foot__quote serif">Cocinamos ideas, servimos emociones.</p>
        <p class="foot__cook">Una experiencia gastronómica única ✦</p>

        <div class="foot__grid">
            <div class="foot__cell">
                <span class="k">Dónde</span>
                <span class="v">CL 12 # 11-62, Centro<br>Cuarto piso · Hotel Ocean Maicao</span>
            </div>
            <div class="foot__cell">
                <span class="k">Horario</span>
                <span class="v">Lunes a Domingo<br>Almuerzo & Noche</span>
            </div>
            <div class="foot__cell">
                <span class="k">Reservas</span>
                <span class="v"><a href="tel:+573122424234">+57 312 242 4234</a><br><a href="https://instagram.com/pisocuatr4" target="_blank" rel="noopener">@pisocuatr4</a></span>
            </div>
        </div>

        <a class="btn-reserve foot__cta" href="https://wa.me/573122424234?text=Hola%20Piso%20Cuatro,%20quisiera%20reservar%20una%20mesa." target="_blank" rel="noopener">
            <span class="dot"></span> Reservar por WhatsApp
        </a>

        <p class="foot__copy">Piso Cuatro <span class="sep">·</span> Restaurante &amp; Bar <span class="sep">·</span> Maicao</p>
    </footer>

    <script src="{{ asset('piso-cuatro-menu/menu-data.js') }}"></script>
    <script src="{{ asset('piso-cuatro-menu/app.js') }}"></script>
    <script src="{{ asset('piso-cuatro-menu/intro.js') }}"></script>
</body>
</html>
