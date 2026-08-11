<?php

declare(strict_types=1);

namespace App\Http\Requests\GlucoseDashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TriagePatientsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'criteria' => ['required', 'string', Rule::in(['low_recent', 'inactive', 'good_control'])],
        ];
    }
}
