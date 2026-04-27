<?php

declare(strict_types=1);

namespace App\Http\Requests\GlucoseRanges;

use App\Models\GlucoseRange;
use Illuminate\Foundation\Http\FormRequest;

class StoreGlucoseRangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create-glucose-ranges', GlucoseRange::class);
    }

    public function rules(): array
    {
        return [
            "alias" => "required|string|max:50|unique:glucose_ranges,alias",
            "min_fasting_value" => "required|numeric|min:60|max:500",
            "max_fasting_value" => "required|numeric|min:60|max:500|gt:min_fasting_value",
            "min_non_fasting_value" => "required|numeric|min:60|max:500",
            "max_non_fasting_value" => "required|numeric|min:60|max:500|gt:min_non_fasting_value",
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El valor es requerido para continuar.',
            'numeric' => 'El valor debe ser numérico.',
            'min' => 'El valor debe ser desde :min.',
            'max' => 'El valor debe ser hasta :max.',
            'gt' => 'El valor máximo debe ser mayor que :value.',
            'unique' => 'El alias ya está en uso.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $exists = GlucoseRange::where('min_fasting_value', $this->input('min_fasting_value'))
                ->where('max_fasting_value', $this->input('max_fasting_value'))
                ->where('min_non_fasting_value', $this->input('min_non_fasting_value'))
                ->where('max_non_fasting_value', $this->input('max_non_fasting_value'))
                ->exists();
            if ($exists) {
                $validator->errors()->add('duplicate', 'Ya existe un rango de glucosa con estos valores.');
            }
        });
    }
}
