<?php

declare(strict_types=1);

namespace App\Http\Requests\GlucoseDashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientSummaryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'period' => ['sometimes', 'integer', Rule::in([7, 30, 90])],
        ];
    }
}
