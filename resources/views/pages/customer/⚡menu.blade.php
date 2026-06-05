<?php

use App\Helpers\Money;
use App\Models\Category;
use App\Models\Mesa;
use App\Models\Product;
use App\Models\SessionParticipant;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\WaiterService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.customer')] #[Title('Menú · Piso Cuatro')] class extends Component
{
    public Mesa $mesa;

    public string $participantName = '';

    public ?int $modalProductId = null;

    public int $modalQty = 1;

    public string $modalNotes = '';

    public bool $showCart = false;

    public function mount(Mesa $mesa): void
    {
        $this->mesa = $mesa;
        $this->participantName = $this->participant()->nombre;
    }

    /** Participante resuelto desde la cookie cifrada (seguro ante manipulación). */
    private function participant(): SessionParticipant
    {
        $token = request()->cookie('participant_token');
        $session = $this->mesa->activeSession;

        $participant = ($token && $session)
            ? SessionParticipant::where('token', $token)
                ->where('restaurant_session_id', $session->id)
                ->first()
            : null;

        abort_unless($participant, 403);

        return $participant;
    }

    #[Computed]
    public function categories(): Collection
    {
        return Category::active()->ordered()
            ->with('availableProducts')
            ->get()
            ->filter(fn (Category $c) => $c->availableProducts->isNotEmpty())
            ->values();
    }

    #[Computed]
    public function cartItems(): Collection
    {
        return $this->participant()->cartItems()->with('product')->latest()->get();
    }

    #[Computed]
    public function cartCount(): int
    {
        return (int) $this->cartItems->sum('quantity');
    }

    #[Computed]
    public function cartTotal(): float
    {
        return $this->cartItems->sum(fn ($i) => (float) ($i->product->price ?? 0) * $i->quantity);
    }

    #[Computed]
    public function modalProduct(): ?Product
    {
        return $this->modalProductId ? Product::find($this->modalProductId) : null;
    }

    public function openProduct(int $productId): void
    {
        $this->modalProductId = $productId;
        $this->modalQty = 1;
        $this->modalNotes = '';
    }

    public function closeModal(): void
    {
        $this->modalProductId = null;
    }

    public function incModalQty(): void
    {
        $this->modalQty = min(50, $this->modalQty + 1);
    }

    public function decModalQty(): void
    {
        $this->modalQty = max(1, $this->modalQty - 1);
    }

    public function addToCart(CartService $cart): void
    {
        $product = $this->modalProduct;

        if (! $product || ! $product->is_available) {
            $this->closeModal();

            return;
        }

        $cart->add($this->participant(), $product, $this->modalQty, $this->modalNotes);
        $this->forgetCart();
        $this->closeModal();

        Flux::toast(text: $product->name.' agregado a tu pedido', variant: 'success', duration: 1500);
    }

    public function changeQty(int $cartItemId, int $delta, CartService $cart): void
    {
        $item = $this->participant()->cartItems()->find($cartItemId);

        if ($item) {
            $cart->setQuantity($item, $item->quantity + $delta);
            $this->forgetCart();
        }
    }

    public function removeItem(int $cartItemId, CartService $cart): void
    {
        $item = $this->participant()->cartItems()->find($cartItemId);

        if ($item) {
            $cart->remove($item);
            $this->forgetCart();
        }
    }

    public function submitOrder(OrderService $orders): void
    {
        if ($this->cartCount < 1) {
            return;
        }

        $orders->submitOrder($this->participant());

        $this->redirectRoute('mesa.orders', ['mesa' => $this->mesa], navigate: true);
    }

    public function callWaiter(WaiterService $waiters): void
    {
        $participant = $this->participant();
        $waiters->call($participant->session, $participant);

        Flux::toast(text: 'Un mesero viene en camino 🔔', variant: 'success', duration: 2000);
    }

    private function forgetCart(): void
    {
        unset($this->cartItems, $this->cartCount, $this->cartTotal);
    }
}; ?>

<div class="relative flex min-h-svh flex-col">
    {{-- Encabezado --}}
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/90 px-5 py-3 backdrop-blur">
        <div>
            <p class="text-xs uppercase tracking-widest text-amber-400/80">Mesa {{ $mesa->numero }}</p>
            <p class="text-sm text-zinc-400">Hola, {{ $participantName }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="callWaiter"
                class="rounded-lg border border-zinc-700 px-3 py-1.5 text-sm text-zinc-200 active:scale-95">
                🔔 Mesero
            </button>
            <a href="{{ route('mesa.orders', $mesa) }}" wire:navigate
               class="rounded-lg border border-zinc-700 px-3 py-1.5 text-sm text-zinc-200 active:scale-95">
                Mis pedidos
            </a>
        </div>
    </header>

    {{-- Menú --}}
    <main class="flex-1 space-y-8 px-5 py-6 pb-28">
        @foreach ($this->categories as $category)
            <section wire:key="cat-{{ $category->id }}">
                <p class="text-xs uppercase tracking-widest text-amber-400/70">{{ $category->kicker }}</p>
                <h2 class="text-2xl font-semibold">{{ $category->name }}</h2>
                @if ($category->subtitle)
                    <p class="mt-1 text-sm text-zinc-500">{{ $category->subtitle }}</p>
                @endif

                @foreach ($category->availableProducts->groupBy('group_label') as $groupLabel => $items)
                    @if ($groupLabel)
                        <h3 class="mt-5 text-sm font-semibold uppercase tracking-wide text-zinc-300">{{ $groupLabel }}</h3>
                    @endif

                    <div class="mt-3 space-y-2">
                        @foreach ($items as $product)
                            <button type="button" wire:key="prod-{{ $product->id }}" wire:click="openProduct({{ $product->id }})"
                                class="flex w-full items-start justify-between gap-3 rounded-xl border border-zinc-800 bg-zinc-900 p-4 text-left transition active:scale-[0.99]">
                                <span class="min-w-0">
                                    <span class="font-medium">
                                        {{ $product->name }}
                                        @if ($product->is_featured)<span class="ml-1 text-amber-400">★</span>@endif
                                    </span>
                                    @if ($product->description)
                                        <span class="mt-1 block text-sm text-zinc-400">{{ $product->description }}</span>
                                    @endif
                                    @if ($product->note)
                                        <span class="mt-1 block text-xs text-zinc-500">{{ $product->note }}</span>
                                    @endif
                                </span>
                                <span class="shrink-0 font-semibold text-amber-400">{{ $product->price_formatted }}</span>
                            </button>
                        @endforeach
                    </div>
                @endforeach
            </section>
        @endforeach
    </main>

    {{-- Barra inferior: ver pedido --}}
    @if ($this->cartCount > 0)
        <div class="fixed inset-x-0 bottom-0 z-20 mx-auto max-w-lg border-t border-zinc-800 bg-zinc-950/95 p-4 backdrop-blur">
            <button type="button" wire:click="$set('showCart', true)"
                class="flex w-full items-center justify-between rounded-xl bg-amber-500 px-5 py-3 font-semibold text-zinc-950 active:scale-[0.99]">
                <span>Ver pedido ({{ $this->cartCount }})</span>
                <span>{{ Money::format($this->cartTotal) }}</span>
            </button>
        </div>
    @endif

    {{-- Modal: ¿agregar producto? --}}
    @if ($this->modalProduct)
        <div class="fixed inset-0 z-40 flex items-end justify-center bg-black/70 sm:items-center" wire:key="add-modal">
            <div class="w-full max-w-lg rounded-t-2xl border-t border-zinc-800 bg-zinc-900 p-6 sm:rounded-2xl sm:border">
                <h3 class="text-lg font-semibold">{{ $this->modalProduct->name }}</h3>
                @if ($this->modalProduct->description)
                    <p class="mt-1 text-sm text-zinc-400">{{ $this->modalProduct->description }}</p>
                @endif
                <p class="mt-2 font-semibold text-amber-400">{{ $this->modalProduct->price_formatted }}</p>

                <div class="mt-5 flex items-center justify-between">
                    <span class="text-sm text-zinc-400">Cantidad</span>
                    <div class="flex items-center gap-4">
                        <button type="button" wire:click="decModalQty" class="size-9 rounded-full border border-zinc-700 text-xl leading-none">−</button>
                        <span class="w-6 text-center text-lg font-semibold">{{ $modalQty }}</span>
                        <button type="button" wire:click="incModalQty" class="size-9 rounded-full border border-zinc-700 text-xl leading-none">+</button>
                    </div>
                </div>

                <textarea wire:model="modalNotes" rows="2" placeholder="Notas (opcional): sin cebolla, término…"
                    class="mt-4 w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm placeholder-zinc-600 focus:border-amber-400 focus:outline-none"></textarea>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <button type="button" wire:click="closeModal"
                        class="rounded-xl border border-zinc-700 px-4 py-3 font-medium text-zinc-200 active:scale-95">
                        Cancelar
                    </button>
                    <button type="button" wire:click="addToCart"
                        class="rounded-xl bg-amber-500 px-4 py-3 font-semibold text-zinc-950 active:scale-95">
                        Agregar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Drawer: carrito / enviar pedido --}}
    @if ($showCart)
        <div class="fixed inset-0 z-40 bg-black/70" wire:click="$set('showCart', false)"></div>
        <div class="fixed inset-x-0 bottom-0 z-50 mx-auto flex max-h-[88vh] max-w-lg flex-col rounded-t-2xl border-t border-zinc-800 bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-800 px-6 py-4">
                <h3 class="text-lg font-semibold">Tu pedido</h3>
                <button type="button" wire:click="$set('showCart', false)" class="text-zinc-400">Cerrar</button>
            </div>

            <div class="flex-1 space-y-3 overflow-y-auto px-6 py-4">
                @forelse ($this->cartItems as $item)
                    <div wire:key="cart-{{ $item->id }}" class="flex items-center justify-between gap-3 rounded-xl border border-zinc-800 p-3">
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ $item->product->name }}</p>
                            @if ($item->notes)
                                <p class="truncate text-xs text-zinc-500">{{ $item->notes }}</p>
                            @endif
                            <p class="text-sm text-amber-400">{{ $item->line_total }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button type="button" wire:click="changeQty({{ $item->id }}, -1)" class="size-8 rounded-full border border-zinc-700 leading-none">−</button>
                            <span class="w-5 text-center">{{ $item->quantity }}</span>
                            <button type="button" wire:click="changeQty({{ $item->id }}, 1)" class="size-8 rounded-full border border-zinc-700 leading-none">+</button>
                            <button type="button" wire:click="removeItem({{ $item->id }})" class="ml-1 text-zinc-500" title="Quitar">✕</button>
                        </div>
                    </div>
                @empty
                    <p class="py-8 text-center text-zinc-500">Tu carrito está vacío.</p>
                @endforelse
            </div>

            @if ($this->cartCount > 0)
                <div class="border-t border-zinc-800 px-6 py-4">
                    <div class="mb-3 flex items-center justify-between text-lg">
                        <span class="text-zinc-400">Total</span>
                        <span class="font-semibold">{{ Money::format($this->cartTotal) }}</span>
                    </div>
                    <button type="button" wire:click="submitOrder" wire:loading.attr="disabled"
                        class="w-full rounded-xl bg-amber-500 px-5 py-3 text-lg font-semibold text-zinc-950 active:scale-[0.99] disabled:opacity-60">
                        Enviar Pedido
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>
