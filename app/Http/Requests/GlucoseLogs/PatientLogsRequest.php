<?php

declare(strict_types=1);

namespace App\Http\Requests\GlucoseLogs;

use Illuminate\Foundation\Http\FormRequest;

class PatientLogsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'date' => ['sometimes', 'date_format:Y-m-d'],
        ];
    }
}
