<?php

use App\Concerns\ResolvesParticipant;
use App\Helpers\Money;
use App\Models\Category;
use App\Models\Mesa;
use App\Models\Product;
use App\Services\CartService;
use App\Services\OrderService;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.customer')] #[Title('Menú · Piso Cuatro')] class extends Component
{
    use ResolvesParticipant;

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

    private function forgetCart(): void
    {
        unset($this->cartItems, $this->cartCount, $this->cartTotal);
    }
}; ?>

<div class="relative flex min-h-svh flex-col">
    {{-- Encabezado --}}
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-zinc-800 bg-zinc-950/90 px-5 py-4 backdrop-blur">
        <div>
            <p class="header-subtitle">Mesa {{ $mesa->numero }}</p>
            <p class="text-sm text-zinc-300">Hola, {{ $participantName }}</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="callWaiter" class="btn-secondary">
                🔔 Mesero
            </button>
            <a href="{{ route('mesa.orders', $mesa) }}" wire:navigate class="btn-secondary">
                Pedidos
            </a>
        </div>
    </header>

    {{-- Menú --}}
    <main class="flex-1 space-y-8 px-5 py-6 pb-28">
        @foreach ($this->categories as $category)
            <section wire:key="cat-{{ $category->id }}">
                <p class="kicker mb-2.5 block">{{ $category->kicker }}</p>
                <h2 class="serif text-3xl font-medium text-[var(--piso-fg)]">{{ $category->name }}</h2>
                @if ($category->subtitle)
                    <p class="mt-1 text-sm text-muted">{{ $category->subtitle }}</p>
                @endif

                @foreach ($category->availableProducts->groupBy('group_label') as $groupLabel => $items)
                    @if ($groupLabel)
                        <h3 class="mt-6 text-sm font-semibold uppercase tracking-wide text-zinc-300">{{ $groupLabel }}</h3>
                    @endif

                    <div class="mt-3">
                        @foreach ($items as $product)
                            <button type="button" wire:key="prod-{{ $product->id }}" wire:click="openProduct({{ $product->id }})"
                                class="group grid w-full grid-cols-[1fr_auto] items-baseline gap-4 border-b border-[var(--piso-line)] py-4 text-left transition active:scale-[0.99]">
                                <span class="min-w-0">
                                    <span class="font-medium text-[var(--piso-fg)] transition group-hover:text-[var(--piso-gold)]">
                                        {{ $product->name }}
                                        @if ($product->is_featured)<span class="ml-1 text-[var(--piso-gold)]">✦</span>@endif
                                    </span>
                                    @if ($product->description)
                                        <span class="mt-1 block text-[13px] font-light leading-snug text-muted">{{ $product->description }}</span>
                                    @endif
                                    @if ($product->note)
                                        <span class="mt-1 block text-muted-sm">{{ $product->note }}</span>
                                    @endif
                                </span>
                                <span class="shrink-0 font-medium tabular-nums text-[var(--piso-silver-1)]">{{ $product->price_formatted }}</span>
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
            <button type="button" wire:click="$set('showCart', true)" class="btn-primary w-full flex items-center justify-between gap-3">
                <span>Ver pedido</span>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-black/30 px-2.5 py-0.5">
                    ({{ $this->cartCount }}) {{ Money::format($this->cartTotal) }}
                </span>
            </button>
        </div>
    @endif

    {{-- Modal: ¿agregar producto? --}}
    @if ($this->modalProduct)
        <div class="fixed inset-0 z-40 flex items-end justify-center bg-black/70 sm:items-center" wire:key="add-modal" wire:click="closeModal">
            <div class="w-full max-w-lg rounded-t-2xl border-t border-zinc-800 bg-zinc-900 p-6 sm:rounded-2xl sm:border space-y-5" wire:click.stop>
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h3 class="serif text-2xl font-medium text-[var(--piso-fg)]">{{ $this->modalProduct->name }}</h3>
                        @if ($this->modalProduct->description)
                            <p class="mt-2 text-sm font-light text-muted">{{ $this->modalProduct->description }}</p>
                        @endif
                        <p class="metal mt-3 text-2xl font-semibold">{{ $this->modalProduct->price_formatted }}</p>
                    </div>
                    <button type="button" wire:click="closeModal" class="flex size-9 shrink-0 items-center justify-center rounded-full border border-zinc-700 text-xl leading-none text-muted transition hover:border-zinc-500 hover:bg-zinc-800 hover:text-zinc-100" aria-label="Cerrar modal">
                        &times;
                    </button>
                </div>

                <div class="flex items-center justify-between rounded-lg border border-zinc-800 bg-zinc-950 p-4">
                    <span class="text-sm font-medium text-zinc-300">Cantidad</span>
                    <div class="flex items-center gap-4">
                        <button type="button" wire:click="decModalQty" class="flex items-center justify-center size-9 rounded-full border border-zinc-700 hover:bg-zinc-800 text-lg leading-none font-bold">−</button>
                        <span class="w-8 text-center text-lg font-bold text-zinc-100">{{ $modalQty }}</span>
                        <button type="button" wire:click="incModalQty" class="flex items-center justify-center size-9 rounded-full border border-zinc-700 hover:bg-zinc-800 text-lg leading-none font-bold">+</button>
                    </div>
                </div>

                <textarea wire:model="modalNotes" rows="2" placeholder="Notas especiales: sin cebolla, punto, etc…"
                    class="input-base w-full resize-none"></textarea>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button type="button" wire:click="closeModal" class="btn-secondary">
                        Cancelar
                    </button>
                    <button type="button" wire:click="addToCart" class="btn-primary">
                        ✓ Agregar
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
                <h3 class="text-lg font-semibold text-zinc-100">Tu pedido</h3>
                <button type="button" wire:click="$set('showCart', false)" class="text-muted hover:text-zinc-300">✕</button>
            </div>

            <div class="flex-1 space-y-2 overflow-y-auto px-4 py-4">
                @forelse ($this->cartItems as $item)
                    <div wire:key="cart-{{ $item->id }}" class="flex items-center justify-between gap-3 rounded-lg border border-zinc-800 bg-zinc-950 p-3 hover:border-amber-700">
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-medium text-zinc-100">{{ $item->product->name }}</p>
                            @if ($item->notes)
                                <p class="truncate text-xs text-muted-sm">{{ $item->notes }}</p>
                            @endif
                            <p class="text-sm text-amber-400 font-semibold mt-1">{{ $item->line_total }}</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" wire:click="changeQty({{ $item->id }}, -1)" class="flex items-center justify-center size-7 rounded-full border border-zinc-700 hover:bg-zinc-800 text-sm leading-none">−</button>
                            <span class="w-6 text-center font-semibold text-zinc-100">{{ $item->quantity }}</span>
                            <button type="button" wire:click="changeQty({{ $item->id }}, 1)" class="flex items-center justify-center size-7 rounded-full border border-zinc-700 hover:bg-zinc-800 text-sm leading-none">+</button>
                            <button type="button" wire:click="removeItem({{ $item->id }})" class="ml-1 text-muted hover:text-red-400" title="Quitar">✕</button>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center py-12 text-muted">
                        <p class="text-sm">Tu carrito está vacío</p>
                    </div>
                @endforelse
            </div>

            @if ($this->cartCount > 0)
                <div class="border-t border-zinc-800 px-4 py-4 space-y-3">
                    <div class="flex items-center justify-between text-lg">
                        <span class="text-muted">Subtotal</span>
                        <span class="font-semibold text-zinc-100">{{ Money::format($this->cartTotal) }}</span>
                    </div>
                    <button type="button" wire:click="submitOrder" wire:loading.attr="disabled"
                        class="btn-primary w-full text-lg py-3 disabled:opacity-60">
                        ✓ Enviar Pedido
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>
