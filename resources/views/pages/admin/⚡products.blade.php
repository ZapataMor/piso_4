<?php

use App\Concerns\AdminOnly;
use App\Enums\PreparationType;
use App\Helpers\Money;
use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Productos · Piso 4')] class extends Component
{
    use AdminOnly;

    public bool $showForm = false;

    public string $search = '';

    public string $catFilter = 'Todas';

    public string $sortKey = 'n';

    public int $sortDir = 1;

    public ?int $editingId = null;

    public string $name = '';

    public ?int $category_id = null;

    public string $description = '';

    public string $price = '';

    public string $tipo_preparacion = 'cocina';

    public string $group_label = '';

    public bool $is_available = true;

    public bool $is_featured = false;

    #[Computed]
    public function categories(): Collection
    {
        return Category::ordered()->get();
    }

    #[Computed]
    public function products(): Collection
    {
        $dir = $this->sortDir < 0 ? 'desc' : 'asc';

        $query = Product::with('category')
            ->when($this->catFilter !== 'Todas',
                fn ($q) => $q->whereHas('category', fn ($q) => $q->where('name', $this->catFilter))
            )
            ->when($this->search !== '', function ($q) {
                $term = trim($this->search);
                $q->where(fn ($q) => $q
                    ->where('name', 'like', "%{$term}%")
                    ->orWhereHas('category', fn ($q) => $q->where('name', 'like', "%{$term}%"))
                );
            });

        if ($this->sortKey === 'p') {
            return $query->orderBy('price', $dir)->get()->values();
        }

        if ($this->sortKey === 'c') {
            $desc = $this->sortDir < 0;

            return $query->get()
                ->sortBy(fn (Product $p) => mb_strtolower($p->category?->name ?? ''), SORT_NATURAL, $desc)
                ->values();
        }

        return $query->orderBy('name', $dir)->get()->values();
    }

    #[Computed]
    public function totalCount(): int
    {
        return Product::count();
    }

    #[Computed]
    public function availableCount(): int
    {
        return Product::where('is_available', true)->count();
    }

    public function sort(string $key): void
    {
        if ($this->sortKey === $key) {
            $this->sortDir *= -1;
        } else {
            $this->sortKey = $key;
            $this->sortDir = 1;
        }
    }

    public function mono(string $name): string
    {
        $skip = ['de', 'con', 'a', 'la', 'el', 'y', '&', 'del', 'los', 'las', 'al', 'en'];
        $words = array_values(array_filter(
            preg_split('/\s+/', trim($name)),
            fn ($w) => $w !== '' && ! in_array(mb_strtolower($w), $skip, true) && preg_match('/[a-záéíóúñ]/iu', $w)
        ));

        $initials = mb_strtoupper(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_slice($words, 0, 2))));

        if (mb_strlen($initials) < 2 && isset($words[0])) {
            $initials = mb_strtoupper(mb_substr($words[0], 0, 2));
        }

        return $initials;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->category_id = $this->categories->first()?->id;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $p = Product::findOrFail($id);
        $this->editingId = $p->id;
        $this->name = $p->name;
        $this->category_id = $p->category_id;
        $this->description = $p->description ?? '';
        $this->price = $p->price !== null ? (string) (int) $p->price : '';
        $this->tipo_preparacion = $p->tipo_preparacion->value;
        $this->group_label = $p->group_label ?? '';
        $this->is_available = $p->is_available;
        $this->is_featured = $p->is_featured;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'tipo_preparacion' => ['required', Rule::in(PreparationType::values())],
            'group_label' => ['nullable', 'string', 'max:255'],
            'is_available' => ['boolean'],
            'is_featured' => ['boolean'],
        ]);

        $data['price'] = $this->price === '' ? null : $this->price;
        $data['group_label'] = $this->group_label !== '' ? $this->group_label : null;

        if ($this->editingId) {
            Product::findOrFail($this->editingId)->update($data);
            $msg = 'Producto actualizado.';
        } else {
            Product::create($data);
            $msg = 'Producto creado.';
        }

        $this->showForm = false;
        unset($this->products);
        Flux::toast(text: $msg, variant: 'success');
    }

    public function toggleAvailable(int $id): void
    {
        $p = Product::find($id);
        $p?->update(['is_available' => ! $p->is_available]);
        unset($this->products);
    }

    public function delete(int $id): void
    {
        Product::find($id)?->delete();
        unset($this->products);
        Flux::toast(text: 'Producto eliminado.', variant: 'success');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->price = '';
        $this->group_label = '';
        $this->tipo_preparacion = 'cocina';
        $this->is_available = true;
        $this->is_featured = false;
    }
}; ?>

<div>
    {{-- Encabezado --}}
    <div class="piso-in">
        <p class="kicker">Administración</p>
        <div class="head-row flex items-end justify-between gap-6 mt-2.5">
            <h1 class="header-title">Productos</h1>
            <button type="button" wire:click="create" class="btn-primary shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nuevo producto
            </button>
        </div>
    </div>
    <div class="piso-rule my-7"></div>

    {{-- Toolbar: búsqueda + chips de categoría --}}
    <div class="ptoolbar piso-in piso-in-1">
        <div class="psearch">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar producto…" autocomplete="off">
        </div>
        <div class="pchips">
            <button type="button" class="pchip {{ $catFilter === 'Todas' ? 'active' : '' }}" wire:click="$set('catFilter', 'Todas')">Todas</button>
            @foreach ($this->categories as $cat)
                <button type="button" class="pchip {{ $catFilter === $cat->name ? 'active' : '' }}" wire:click="$set('catFilter', @js($cat->name))">{{ $cat->name }}</button>
            @endforeach
        </div>
    </div>

    {{-- Tabla --}}
    <div class="ptable piso-in piso-in-2">
        <div class="ptable__head">
            <button type="button" class="pth sortable {{ $sortKey === 'n' ? 'sorted' : '' }} {{ $sortKey === 'n' && $sortDir < 0 ? 'desc' : '' }}" wire:click="sort('n')">
                Producto <svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="18 15 12 9 6 15"/></svg>
            </button>
            <button type="button" class="pth sortable {{ $sortKey === 'c' ? 'sorted' : '' }} {{ $sortKey === 'c' && $sortDir < 0 ? 'desc' : '' }}" wire:click="sort('c')">
                Categoría <svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="18 15 12 9 6 15"/></svg>
            </button>
            <div class="pth">Estación</div>
            <button type="button" class="pth sortable pth--r {{ $sortKey === 'p' ? 'sorted' : '' }} {{ $sortKey === 'p' && $sortDir < 0 ? 'desc' : '' }}" wire:click="sort('p')">
                Precio <svg class="caret" viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="18 15 12 9 6 15"/></svg>
            </button>
            <div class="pth">Disponible</div>
            <div class="pth pth--r">Acciones</div>
        </div>

        @forelse ($this->products as $product)
            <div wire:key="prod-{{ $product->id }}" class="prow {{ $product->is_available ? '' : 'is-off' }}">
                <div class="pname">
                    <span class="pmono"><span>{{ $this->mono($product->name) }}</span></span>
                    <span class="pname__t">
                        {{ $product->name }}
                        @if ($product->is_featured)<span class="pname__star">★</span>@endif
                    </span>
                </div>
                <div><span class="pcat">{{ $product->category->name }}</span></div>
                <div><span class="pstation"><span class="d"></span>{{ $product->tipo_preparacion->label() }}</span></div>
                <div class="pprice metal">{{ $product->price_formatted }}</div>
                <div>
                    <button type="button" class="ptoggle {{ $product->is_available ? 'on' : '' }}" wire:click="toggleAvailable({{ $product->id }})" role="switch" aria-checked="{{ $product->is_available ? 'true' : 'false' }}">
                        <span class="ptoggle__track"><span class="ptoggle__knob"></span></span>
                        <span class="ptoggle__lbl">{{ $product->is_available ? 'Disponible' : 'Agotado' }}</span>
                    </button>
                </div>
                <div class="pacts">
                    <button type="button" class="pact pact--edit" title="Editar" wire:click="edit({{ $product->id }})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                    </button>
                    <button type="button" class="pact pact--del" title="Eliminar" wire:click="delete({{ $product->id }})" wire:confirm="¿Eliminar {{ $product->name }}?">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6"/><path d="M10 11v6M14 11v6"/></svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="pempty">Sin resultados para tu búsqueda.</div>
        @endforelse
    </div>

    <div class="pfoot piso-in piso-in-2">
        Mostrando <b>{{ $this->products->count() }}</b> de <b>{{ $this->totalCount }}</b> productos · <b>{{ $this->availableCount }}</b> disponibles
    </div>

    {{-- Formulario --}}
    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4" wire:key="product-form">
            <div class="w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900" style="max-height: 90vh">
                <flux:heading size="lg" class="mb-4">{{ $editingId ? 'Editar producto' : 'Nuevo producto' }}</flux:heading>

                <form wire:submit="save" class="flex flex-col gap-4">
                    <flux:input wire:model="name" label="Nombre" required />

                    <flux:select wire:model="category_id" label="Categoría">
                        @foreach ($this->categories as $cat)
                            <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:textarea wire:model="description" label="Descripción" rows="2" />

                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="price" label="Precio (COP)" type="number" min="0" placeholder="Vacío = sin precio" />
                        <flux:select wire:model="tipo_preparacion" label="Estación">
                            <flux:select.option value="cocina">Cocina</flux:select.option>
                            <flux:select.option value="bar">Bar</flux:select.option>
                        </flux:select>
                    </div>

                    <flux:input wire:model="group_label" label="Subgrupo (opcional)" placeholder="Ej: Menú Infantil, Whiskey…" />

                    <div class="flex gap-6">
                        <flux:checkbox wire:model="is_available" label="Disponible" />
                        <flux:checkbox wire:model="is_featured" label="Destacado" />
                    </div>

                    <div class="mt-2 flex justify-end gap-3">
                        <flux:button type="button" wire:click="$set('showForm', false)" variant="ghost">Cancelar</flux:button>
                        <flux:button type="submit" variant="primary">Guardar</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
