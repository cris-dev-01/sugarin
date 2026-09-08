<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->user()->id,
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El valor es requerido para continuar.',
            'max' => 'El valor debe ser hasta :max caracteres.',
            'unique' => 'Este valor ya está en uso.',
            'email' => 'El valor debe ser un correo electrónico válido.',
        ];
    }
}
