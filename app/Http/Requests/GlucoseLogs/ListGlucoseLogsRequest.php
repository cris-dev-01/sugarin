<?php

declare(strict_types=1);

namespace App\Http\Requests\GlucoseLogs;

use Illuminate\Foundation\Http\FormRequest;

class ListGlucoseLogsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page'                       => ['nullable', 'integer', 'min:1'],
            'per_page'                   => ['nullable', 'integer', 'min:1', 'max:100'],
            'filters.user_patient_id'    => ['nullable', 'integer', 'exists:user_patients,id'],
        ];
    }
}
