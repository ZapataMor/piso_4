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

    public string $search = '';

    public string $roleFilter = 'Todos';

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
        $items = User::with('role')->orderBy('name')->get();

        if ($this->roleFilter !== 'Todos') {
            $items = $items->filter(fn (User $u) => $u->role?->name === $this->roleFilter);
        }

        if ($this->search !== '') {
            $q = mb_strtolower(trim($this->search));
            $items = $items->filter(fn (User $u) => str_contains(mb_strtolower($u->name), $q)
                || str_contains(mb_strtolower($u->email), $q)
                || str_contains(mb_strtolower($u->phone ?? ''), $q)
                || str_contains(mb_strtolower($u->role?->name ?? ''), $q));
        }

        return $items->values();
    }

    #[Computed]
    public function roles(): Collection
    {
        return Role::orderBy('name')->get();
    }

    #[Computed]
    public function totalCount(): int
    {
        return User::count();
    }

    #[Computed]
    public function activeCount(): int
    {
        return User::where('is_active', true)->count();
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

<div>
    <div class="piso-in">
        <p class="kicker">Administración</p>
        <div class="head-row mt-2.5 flex items-end justify-between gap-6">
            <h1 class="header-title">Usuarios</h1>
            <button type="button" wire:click="create" class="btn-primary shrink-0">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="size-4" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Nuevo usuario
            </button>
        </div>
    </div>
    <div class="piso-rule my-7"></div>

    <div class="ptoolbar piso-in piso-in-1">
        <div class="psearch">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar usuario..." autocomplete="off">
        </div>
        <div class="pchips">
            <button type="button" class="pchip {{ $roleFilter === 'Todos' ? 'active' : '' }}" wire:click="$set('roleFilter', 'Todos')">Todos</button>
            @foreach ($this->roles as $role)
                <button type="button" class="pchip {{ $roleFilter === $role->name ? 'active' : '' }}" wire:click="$set('roleFilter', @js($role->name))">{{ $role->name }}</button>
            @endforeach
        </div>
    </div>

    <div class="ptable piso-in piso-in-2" style="--ptpl: minmax(240px, 1fr) minmax(220px, 1fr) 140px 158px 90px;">
        <div class="ptable__head">
            <div class="pth">Usuario</div>
            <div class="pth">Email</div>
            <div class="pth">Rol</div>
            <div class="pth">Activo</div>
            <div class="pth pth--r">Acciones</div>
        </div>

        @forelse ($this->users as $user)
            <div wire:key="user-{{ $user->id }}" class="prow {{ $user->is_active ? '' : 'is-off' }}">
                <div class="pname">
                    <span class="pmono"><span>{{ $this->mono($user->name) }}</span></span>
                    <span class="pstack">
                        <span class="pname__t">{{ $user->name }}</span>
                        <span class="pname__sub">{{ $user->phone ?? 'Sin teléfono registrado' }}</span>
                    </span>
                </div>
                <div class="pmuted">{{ $user->email }}</div>
                <div><span class="pcat is-warm">{{ $user->role?->name ?? 'Sin rol' }}</span></div>
                <div>
                    <button type="button" class="ptoggle {{ $user->is_active ? 'on' : '' }}" wire:click="toggleActive({{ $user->id }})" role="switch" aria-checked="{{ $user->is_active ? 'true' : 'false' }}">
                        <span class="ptoggle__track"><span class="ptoggle__knob"></span></span>
                        <span class="ptoggle__lbl">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</span>
                    </button>
                </div>
                <div class="pacts">
                    <button type="button" class="pact pact--edit" title="Editar" wire:click="edit({{ $user->id }})">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                    </button>
                    <button type="button" class="pact pact--del" title="Eliminar" wire:click="delete({{ $user->id }})" wire:confirm="¿Eliminar a {{ $user->name }}?">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m2 0v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6"/><path d="M10 11v6M14 11v6"/></svg>
                    </button>
                </div>
            </div>
        @empty
            <div class="pempty">Sin usuarios para mostrar.</div>
        @endforelse
    </div>

    <div class="pfoot piso-in piso-in-2">
        Mostrando <b>{{ $this->users->count() }}</b> de <b>{{ $this->totalCount }}</b> usuarios · <b>{{ $this->activeCount }}</b> activos
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
