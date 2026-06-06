<?php

use App\Concerns\AdminOnly;
use App\Models\Role;
use App\Models\User;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Usuarios · Piso 4')] class extends Component
{
    use AdminOnly;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public ?int $role_id = null;

    public string $phone = '';

    public bool $is_active = true;

    #[Computed]
    public function users(): Collection
    {
        return User::with('role')->orderBy('name')->get();
    }

    #[Computed]
    public function roles(): Collection
    {
        return Role::orderBy('name')->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->role_id = $this->roles->firstWhere('slug.value', 'mesero')?->id ?? $this->roles->first()?->id;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $u = User::findOrFail($id);
        $this->editingId = $u->id;
        $this->name = $u->name;
        $this->email = $u->email;
        $this->password = '';
        $this->role_id = $u->role_id;
        $this->phone = $u->phone ?? '';
        $this->is_active = (bool) $u->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'password' => [$this->editingId ? 'nullable' : 'required', 'nullable', 'string', 'min:8'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $data['phone'] = $this->phone !== '' ? $this->phone : null;

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            unset($data['password']);
            $user->update($data);
            if ($this->password !== '') {
                $user->update(['password' => $this->password]); // se hashea por el cast
            }
            $msg = 'Usuario actualizado.';
        } else {
            $data['email_verified_at'] = now();
            User::create($data); // password se hashea por el cast
            $msg = 'Usuario creado.';
        }

        $this->showForm = false;
        unset($this->users);
        Flux::toast(text: $msg, variant: 'success');
    }

    public function toggleActive(int $id): void
    {
        if ($id === auth()->id()) {
            Flux::toast(text: 'No puedes desactivar tu propia cuenta.', variant: 'danger');

            return;
        }

        $u = User::find($id);
        $u?->update(['is_active' => ! $u->is_active]);
        unset($this->users);
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            Flux::toast(text: 'No puedes eliminar tu propia cuenta.', variant: 'danger');

            return;
        }

        User::find($id)?->delete();
        unset($this->users);
        Flux::toast(text: 'Usuario eliminado.', variant: 'success');
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->phone = '';
        $this->is_active = true;
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Usuarios</flux:heading>
        <flux:button wire:click="create" icon="plus" variant="primary">Nuevo usuario</flux:button>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3 font-medium">Nombre</th>
                    <th class="px-4 py-3 font-medium">Email</th>
                    <th class="px-4 py-3 font-medium">Rol</th>
                    <th class="px-4 py-3 font-medium">Activo</th>
                    <th class="px-4 py-3 text-right font-medium">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($this->users as $user)
                    <tr wire:key="user-{{ $user->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <flux:badge size="sm">{{ $user->role?->name ?? 'Sin rol' }}</flux:badge>
                        </td>
                        <td class="px-4 py-3">
                            <button type="button" wire:click="toggleActive({{ $user->id }})">
                                <flux:badge size="sm" :color="$user->is_active ? 'green' : 'zinc'">
                                    {{ $user->is_active ? 'Sí' : 'No' }}
                                </flux:badge>
                            </button>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" wire:click="edit({{ $user->id }})" icon="pencil-square" variant="ghost" />
                                <flux:button size="sm" wire:click="delete({{ $user->id }})" wire:confirm="¿Eliminar a {{ $user->name }}?" icon="trash" variant="danger" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-zinc-500">No hay usuarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4" wire:key="user-form">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">{{ $editingId ? 'Editar usuario' : 'Nuevo usuario' }}</flux:heading>

                <form wire:submit="save" class="flex flex-col gap-4">
                    <flux:input wire:model="name" label="Nombre" required />
                    <flux:input wire:model="email" label="Email" type="email" required />
                    <flux:input wire:model="password" label="Contraseña {{ $editingId ? '(dejar vacío para no cambiar)' : '' }}" type="password" />
                    <div class="grid grid-cols-2 gap-4">
                        <flux:select wire:model="role_id" label="Rol">
                            @foreach ($this->roles as $role)
                                <flux:select.option value="{{ $role->id }}">{{ $role->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input wire:model="phone" label="Teléfono (opcional)" />
                    </div>
                    <flux:checkbox wire:model="is_active" label="Cuenta activa" />
                    <div class="mt-2 flex justify-end gap-3">
                        <flux:button type="button" wire:click="$set('showForm', false)" variant="ghost">Cancelar</flux:button>
                        <flux:button type="submit" variant="primary">Guardar</flux:button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
