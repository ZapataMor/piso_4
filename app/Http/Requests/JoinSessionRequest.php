<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // flujo público: el cliente no necesita cuenta
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:40'],
        ];
    }

    public function attributes(): array
    {
        return ['nombre' => 'nombre'];
    }
}
