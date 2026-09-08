<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El valor es requerido para continuar.',
            'min' => 'El valor debe tener al menos :min caracteres.',
            'confirmed' => 'La confirmación de la clave no coincide.',
        ];
    }
}
