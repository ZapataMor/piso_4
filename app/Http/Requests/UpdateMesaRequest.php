<?php

namespace App\Http\Requests;

use App\Enums\TableStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMesaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('mesas.manage');
    }

    public function rules(): array
    {
        $mesaId = $this->route('mesa')->id;

        return [
            'numero' => ['required', 'integer', 'min:1', Rule::unique('mesas', 'numero')->ignore($mesaId)],
            'nombre' => ['nullable', 'string', 'max:255'],
            'capacidad' => ['nullable', 'integer', 'min:1', 'max:100'],
            'estado' => ['required', Rule::in(TableStatus::values())],
        ];
    }

    public function attributes(): array
    {
        return [
            'numero' => 'número de mesa',
            'nombre' => 'nombre',
            'capacidad' => 'capacidad',
            'estado' => 'estado',
        ];
    }
}
