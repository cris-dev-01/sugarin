<?php

declare(strict_types=1);

namespace App\Http\Requests\GlucoseLogs;

use Illuminate\Foundation\Http\FormRequest;

class StoreGlucoseLogRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_patient_id' => ['required', 'integer', 'exists:user_patients,id'],
            'value' => ['required', 'integer', 'min:1'],
        ];
    }
}
