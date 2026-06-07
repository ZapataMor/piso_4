<?php

use App\Concerns\AdminOnly;
use App\Models\Mesa;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Mesas y QR · Piso 4')] class extends Component
{
    use AdminOnly;

    /** Reverb: al abrir/cerrar una sesión (escaneo de QR) la mesa cambia de
     * estado; refrescamos el listado para reflejar Disponible ⇄ Ocupada. */
    #[On('echo-private:waiters,.session.changed')]
    public function onRealtime(): void
    {
        unset($this->mesas);
    }

    #[Computed]
    public function mesas(): Collection
    {
        return Mesa::orderBy('numero')->get();
    }
}; ?>

<div>
    <div class="piso-in">
        <p class="kicker">Administración</p>
        <div class="head-row mt-2.5 flex items-end justify-between gap-6">
            <div>
                <h1 class="header-title">Mesas y QR</h1>
                <p class="mt-2 text-muted-sm">Administra las mesas y sus códigos QR.</p>
            </div>
            <a href="{{ route('admin.mesas.create') }}" wire:navigate class="btn-primary shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nueva mesa
            </a>
        </div>
    </div>
    <div class="piso-rule my-7"></div>

    @if (session('status'))
        <flux:callout icon="check-circle" variant="success" class="!my-0 piso-in piso-in-1">
            <flux:callout.text>{{ session('status') }}</flux:callout.text>
        </flux:callout>
    @endif

    <div class="ptable piso-in piso-in-2 mt-6" style="--ptpl: 96px minmax(240px, 1fr) 160px 128px 168px;">
        <div class="ptable__head">
            <div class="pth">Mesa</div>
            <div class="pth">Nombre</div>
            <div class="pth">Estado</div>
            <div class="pth">Capacidad</div>
            <div class="pth pth--r">Acciones</div>
        </div>

        @forelse ($this->mesas as $mesa)
            <div class="prow" wire:key="mesa-{{ $mesa->id }}">
                <div class="pname">
                    <span class="pmono"><span>{{ $mesa->numero }}</span></span>
                </div>
                <div class="pstack">
                    <span class="pname__t">{{ $mesa->nombre ?? 'Mesa #' . $mesa->numero }}</span>
                    <span class="pname__sub">QR público de acceso al menú</span>
                </div>
                <div><span class="pstatus">{{ $mesa->estado->label() }}</span></div>
                <div><span class="pcat">{{ $mesa->capacidad ?? '—' }} personas</span></div>
                <div class="pacts">
                    <a href="{{ route('admin.mesas.edit', $mesa) }}" wire:navigate class="pact pact--edit" title="Editar">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                    </a>
                    <a href="{{ route('admin.mesas.qr', $mesa) }}" class="pact pact--qr" title="Descargar QR">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
                    </a>
                    <button type="button" class="pact pact--qr" title="Copiar link del QR" data-copy-qr-url="{{ $mesa->public_url }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                    </button>
                    <form method="POST" action="{{ route('admin.mesas.destroy', $mesa) }}" onsubmit="return confirm('¿Eliminar la mesa #{{ $mesa->numero }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="pact pact--del" title="Eliminar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6"/><path d="M10 11v6M14 11v6"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="pempty">Aún no hay mesas. Crea la primera con "Nueva mesa".</div>
        @endforelse
    </div>

    <div class="pfoot piso-in piso-in-2">
        @php($availableMesas = $this->mesas->filter(fn ($mesa) => $mesa->estado->value === 'disponible')->count())
        Mostrando <b>{{ $this->mesas->count() }}</b> mesas · <b>{{ $availableMesas }}</b> disponibles
    </div>

    <script>
        if (!window.pisoQrCopyHandlerReady) {
            window.pisoQrCopyHandlerReady = true;

            document.addEventListener('click', async (event) => {
                const button = event.target.closest('[data-copy-qr-url]');

                if (!button) {
                    return;
                }

                const url = button.dataset.copyQrUrl;
                const originalTitle = button.getAttribute('title') || 'Copiar link del QR';

                try {
                    if (navigator.clipboard && window.isSecureContext) {
                        await navigator.clipboard.writeText(url);
                    } else {
                        const input = document.createElement('textarea');
                        input.value = url;
                        input.setAttribute('readonly', '');
                        input.style.position = 'fixed';
                        input.style.opacity = '0';
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        input.remove();
                    }

                    button.setAttribute('title', 'Link copiado');
                    window.setTimeout(() => button.setAttribute('title', originalTitle), 1800);
                } catch (error) {
                    button.setAttribute('title', 'No se pudo copiar');
                    window.setTimeout(() => button.setAttribute('title', originalTitle), 1800);
                }
            });
        }
    </script>
</div>
