<?php

declare(strict_types=1);

namespace App\Http\Requests\Patients;

use Illuminate\Foundation\Http\FormRequest;

class SearchPatientsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:255'],
        ];
    }
}
