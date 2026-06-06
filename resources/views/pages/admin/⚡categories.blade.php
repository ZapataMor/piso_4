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
        return Category::ordered()->withCount('products')->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->display_order = ($this->categories->max('display_order') ?? 0) + 1;
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

<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Categorías</flux:heading>
        <flux:button wire:click="create" icon="plus" variant="primary">Nueva categoría</flux:button>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Orden</th>
                    <th class="px-4 py-3 font-medium">Nombre</th>
                    <th class="px-4 py-3 font-medium">Productos</th>
                    <th class="px-4 py-3 font-medium">Activa</th>
                    <th class="px-4 py-3 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->categories as $category)
                    <tr wire:key="cat-{{ $category->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3 text-zinc-500">{{ $category->display_order }}</td>
                        <td class="px-4 py-3 font-medium">{{ $category->name }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $category->products_count }}</td>
                        <td class="px-4 py-3">
                            <button type="button" wire:click="toggleActive({{ $category->id }})">
                                <flux:badge size="sm" :color="$category->is_active ? 'green' : 'zinc'">
                                    {{ $category->is_active ? 'Sí' : 'No' }}
                                </flux:badge>
                            </button>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" wire:click="edit({{ $category->id }})" icon="pencil-square" variant="ghost" />
                                <flux:button size="sm" wire:click="delete({{ $category->id }})" wire:confirm="¿Eliminar {{ $category->name }}?" icon="trash" variant="danger" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-zinc-500">No hay categorías.</td></tr>
                @endforelse
            </tbody>
        </table>
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
