<?php

declare(strict_types=1);

namespace App\Http\Requests\Patients;

use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-patients', User::class);
    }

    public function rules(): array
    {
        return [
            "glucose_range_id" => "required|numeric|exists:glucose_ranges,id",
            "name" => "required|string|max:255|unique:users,name",
            "email" => "required|email|max:255|unique:users,email",
            "document" => "required|max:12|unique:user_patients,document",
            "illness_found_at" => "required|date",
            "initial_max_glucose_value" => "required|numeric|min:0"
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El valor es requerido para continuar.',
            'numeric' => 'El valor debe ser numérico.',
            'min' => 'El valor debe ser desde :min.',
            'max' => 'El valor debe ser hasta :max caracteres.',
            'unique' => 'Este valor ya está en uso.',
            'exists' => 'El valor seleccionado no es válido.',
            'email' => 'El valor debe ser un correo electrónico válido.',
        ];
    }
}
