<?php

use App\Concerns\AdminOnly;
use App\Models\Setting;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Configuración · Piso 4')] class extends Component
{
    use AdminOnly;

    /** @var array<string,string> */
    public array $values = [];

    /** @return array<string, array{label: string, group: string, hint?: string}> */
    public function fields(): array
    {
        return [
            'restaurant_name' => ['label' => 'Nombre del restaurante', 'group' => 'General'],
            'whatsapp_number' => ['label' => 'WhatsApp del restaurante', 'group' => 'WhatsApp', 'hint' => 'Solo dígitos, con indicativo. Ej: 573001234567'],
            'bank_name' => ['label' => 'Banco', 'group' => 'Datos bancarios'],
            'bank_account_type' => ['label' => 'Tipo de cuenta', 'group' => 'Datos bancarios'],
            'bank_account' => ['label' => 'Número de cuenta', 'group' => 'Datos bancarios'],
            'bank_holder' => ['label' => 'Titular', 'group' => 'Datos bancarios'],
            'bank_doc' => ['label' => 'NIT / CC', 'group' => 'Datos bancarios'],
        ];
    }

    public function mount(): void
    {
        foreach (array_keys($this->fields()) as $key) {
            $this->values[$key] = (string) Setting::get($key, '');
        }
    }

    public function save(): void
    {
        $this->validate([
            'values.restaurant_name' => ['required', 'string', 'max:255'],
            'values.whatsapp_number' => ['nullable', 'string', 'max:30'],
            'values.*' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($this->fields() as $key => $meta) {
            Setting::set($key, $this->values[$key] ?? '', 'string', $meta['group']);
        }

        Flux::toast(text: 'Configuración guardada.', variant: 'success');
    }
}; ?>

<div class="mx-auto flex w-full max-w-2xl flex-col gap-6">
    <flux:heading size="xl">Configuración</flux:heading>

    <form wire:submit="save" class="flex flex-col gap-6">
        @foreach (collect($this->fields())->groupBy('group') as $group => $items)
            <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
                <flux:heading size="lg" class="mb-4">{{ $group }}</flux:heading>
                <div class="flex flex-col gap-4">
                    @foreach ($this->fields() as $key => $meta)
                        @if ($meta['group'] === $group)
                            <flux:field>
                                <flux:label>{{ $meta['label'] }}</flux:label>
                                <flux:input wire:model="values.{{ $key }}" />
                                @isset($meta['hint'])
                                    <flux:description>{{ $meta['hint'] }}</flux:description>
                                @endisset
                                <flux:error name="values.{{ $key }}" />
                            </flux:field>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary" icon="check">Guardar configuración</flux:button>
        </div>
    </form>
</div>
