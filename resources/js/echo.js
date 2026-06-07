import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const reverbEnabled = import.meta.env.VITE_REVERB_ENABLED === 'true';
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost = import.meta.env.VITE_REVERB_HOST;
const reverbPort = import.meta.env.VITE_REVERB_PORT;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'https';
const forceTLS = reverbScheme === 'https';

if (reverbEnabled && reverbKey && reverbHost) {
    window.Pusher = Pusher;

    // Cliente Echo apuntando a Laravel Reverb (protocolo Pusher).
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: reverbKey,
        wsHost: reverbHost,
        wsPort: Number(reverbPort ?? 80),
        wssPort: Number(reverbPort ?? 443),
        forceTLS,
        enabledTransports: forceTLS ? ['wss'] : ['ws'],
    });
}
// Si Reverb está deshabilitado dejamos window.Echo como `undefined`
// (no `null`): Livewire solo omite los listeners `echo-*` cuando
// `typeof window.Echo === "undefined"`. Asignar `null` pasaba ese guard y
// reventaba en `null.private(...)`, abortando la init del componente y
// dejando los wire:click sin responder. Los guards `window.Echo && …` de
// Alpine siguen funcionando igual con `undefined`.
