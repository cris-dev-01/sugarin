<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDashboardSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update-dashboard-settings', Setting::class);
    }

    public function rules(): array
    {
        return [
            'recent_event_window_hours' => 'required|integer|min:1|max:168',
            'good_control_threshold' => 'required|numeric|min:0|max:1',
            'good_control_reference_period_days' => 'required|integer|min:1|max:365',
            'expected_logs_per_day' => 'required|integer|min:1|max:24',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'El valor es requerido para continuar.',
            'integer' => 'El valor debe ser un número entero.',
            'numeric' => 'El valor debe ser numérico.',
            'min' => 'El valor debe ser desde :min.',
            'max' => 'El valor debe ser hasta :max.',
        ];
    }
}
