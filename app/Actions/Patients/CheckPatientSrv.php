<?php

declare(strict_types=1);

namespace App\Actions\Patients;

use App\DataTransferObjects\Patients\CheckPatientDto;
use App\Enums\DocumentTypes;
use App\Models\User;
use App\Traits\FormatsDocument;
use Lorisleiva\Actions\Concerns\AsAction;

class CheckPatientSrv
{
    use AsAction, FormatsDocument;

    public function handle(CheckPatientDto $dto): bool
    {
        return $this->exists($this->sanitizeDocumentWithoutVerificator($dto->document), $dto->exclude);
    }

    private function exists(string $document, ?int $exclude): bool
    {
        return User::whereHas('patient', function ($query) use ($document) {
            $query->where('document_type', DocumentTypes::RUT->value)
                ->where('document', $document);
        })
        ->when($exclude, fn ($q) => $q->where('id', '!=', $exclude))
        ->exists();
    }
}
