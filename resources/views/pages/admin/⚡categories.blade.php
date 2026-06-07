<?php

use App\Concerns\AdminOnly;
use App\Models\Category;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Categorías · Piso 4')] class extends Component
{
    use AdminOnly;

    public bool $showForm = false;

    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public string $slug = '';

    public string $kicker = '';

    public string $subtitle = '';

    public int $display_order = 0;

    public bool $is_active = true;

    #[Computed]
    public function categories(): Collection
    {
        $items = Category::ordered()->withCount('products')->get();

        if ($this->search !== '') {
            $q = mb_strtolower(trim($this->search));
            $items = $items->filter(fn (Category $c) => str_contains(mb_strtolower($c->name), $q)
                || str_contains(mb_strtolower($c->slug), $q)
                || str_contains(mb_strtolower($c->kicker ?? ''), $q)
                || str_contains(mb_strtolower($c->subtitle ?? ''), $q));
        }

        return $items->values();
    }

    #[Computed]
    public function totalCount(): int
    {
        return Category::count();
    }

    #[Computed]
    public function activeCount(): int
    {
        return Category::where('is_active', true)->count();
    }

    public function mono(string $name): string
    {
        $words = array_values(array_filter(preg_split('/\s+/', trim($name)), fn ($w) => $w !== ''));
        $initials = mb_strtoupper(implode('', array_map(fn ($w) => mb_substr($w, 0, 1), array_slice($words, 0, 2))));

        if (mb_strlen($initials) < 2 && isset($words[0])) {
            $initials = mb_strtoupper(mb_substr($words[0], 0, 2));
        }

        return $initials;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->display_order = (Category::max('display_order') ?? 0) + 1;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $c = Category::findOrFail($id);
        $this->editingId = $c->id;
        $this->name = $c->name;
        $this->slug = $c->slug;
        $this->kicker = $c->kicker ?? '';
        $this->subtitle = $c->subtitle ?? '';
        $this->display_order = $c->display_order;
        $this->is_active = $c->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->slug = $this->slug !== '' ? Str::slug($this->slug) : Str::slug($this->name);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($this->editingId)],
            'kicker' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'display_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $data['kicker'] = $this->kicker !== '' ? $this->kicker : null;
        $data['subtitle'] = $this->subtitle !== '' ? $this->subtitle : null;

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
            $msg = 'Categoría actualizada.';
        } else {
            Category::create($data);
            $msg = 'Categoría creada.';
        }

        $this->showForm = false;
        unset($this->categories);
        Flux::toast(text: $msg, variant: 'success');
    }

    public function toggleActive(int $id): void
    {
        $c = Category::find($id);
        $c?->update(['is_active' => ! $c->is_active]);
        unset($this->categories);
    }

    public function delete(int $id): void
    {
        $c = Category::withCount('products')->find($id);

        if ($c && $c->products_count > 0) {
            Flux::toast(text: 'No se puede eliminar: la categoría tiene productos.', variant: 'danger');

            return;
        }

        $c?->delete();
        unset($this->categories);
        Flux::toast(text: 'Categoría eliminada.', variant: 'success');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->kicker = '';
        $this->subtitle = '';
        $this->is_active = true;
    }
}; ?>

<div>
    <div class="piso-in">
        <p class="kicker">Administración</p>
        <div class="head-row mt-2.5 flex items-end justify-between gap-6">
            <h1 class="header-title">Categorías</h1>
            <button type="button" wire:click="create" class="btn-primary shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nueva categoría
            </button>
        </div>
    </div>
    <div class="piso-rule my-7"></div>

    <div class="ptoolbar piso-in piso-in-1">
        <div class="psearch">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar categoría..." autocomplete="off">
        </div>
        <div class="pchips">
            <span class="pchip active">Orden del menú</span>
        </div>
    </div>

    <div class="ptable piso-in piso-in-2" style="--ptpl: 92px minmax(260px, 1fr) 128px 158px 90px;">
        <div class="ptable__head">
            <div class="pth">Orden</div>
            <div class="pth">Categoría</div>
            <div class="pth">Productos</div>
            <div class="pth">Activa</div>
            <div class="pth pth--r">Acciones</div>
        </div>

        @forelse ($this->categories as $category)
            <div wire:key="cat-{{ $category->id }}" class="prow {{ $category->is_active ? '' : 'is-off' }}">
                <div class="pvalue">#{{ $category->display_order }}</div>
                <div class="pname">
                    <span class="pmono"><span>{{ $this->mono($category->name) }}</span></span>
                    <span class="pstack">
                        <span class="pname__t">{{ $category->name }}</span>
                        <span class="pname__sub">{{ $category->kicker ?? $category->subtitle ?? $category->slug }}</span>
                    </span>
                </div>
                <div><span class="pcat">{{ $category->products_count }} productos</span></div>
                <div>
                    <button type="button" class="ptoggle {{ $category->is_active ? 'on' : '' }}" wire:click="toggleActive({{ $category->id }})" role="switch" aria-checked="{{ $category->is_active ? 'true' : 'false' }}">
                        <span class="ptoggle__track"><span class="ptoggle__knob"></span></span>
                        <span class="ptoggle__lbl">{{ $category->is_active ? 'Activa' : 'Inactiva' }}</span>
                    </button>
                </div>
                <div class="pacts">
                    <button type="button" class="pact pact--edit" title="Editar" wire:click="edit({{ $category->id }})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                    </button>
                    <button type="button" class="pact pact--del" title="Eliminar" wire:click="delete({{ $category->id }})" wire:confirm="¿Eliminar {{ $category->name }}?">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6"/><path d="M10 11v6M14 11v6"/></svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="pempty">Sin categorías para mostrar.</div>
        @endforelse
    </div>

    <div class="pfoot piso-in piso-in-2">
        Mostrando <b>{{ $this->categories->count() }}</b> de <b>{{ $this->totalCount }}</b> categorías · <b>{{ $this->activeCount }}</b> activas
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4" wire:key="cat-form">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">{{ $editingId ? 'Editar categoría' : 'Nueva categoría' }}</flux:heading>

                <form wire:submit="save" class="flex flex-col gap-4">
                    <flux:input wire:model="name" label="Nombre" required />
                    <flux:input wire:model="slug" label="Slug (opcional)" placeholder="se genera del nombre" />
                    <flux:input wire:model="kicker" label="Kicker (opcional)" placeholder="Capítulo I · Para comenzar" />
                    <flux:input wire:model="subtitle" label="Subtítulo (opcional)" />
                    <div class="grid grid-cols-2 items-end gap-4">
                        <flux:input wire:model="display_order" label="Orden" type="number" min="0" />
                        <flux:checkbox wire:model="is_active" label="Activa" />
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
