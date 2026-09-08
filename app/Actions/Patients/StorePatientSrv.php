<?php

declare(strict_types=1);

namespace App\Actions\Patients;

use App\DataTransferObjects\Patients\StorePatientDto;
use App\Enums\DocumentTypes;
use App\Models\User;
use App\Traits\FormatsDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class StorePatientSrv
{
    use AsAction, FormatsDocument;

    private User $user;

    /**
     * @throws Throwable
     */
    public function handle(StorePatientDto $dto): User|Model
    {
        DB::transaction(function () use ($dto) {
            $this->store($dto)
                ->storePatient(
                    $dto->only('glucose_range_id', 'illness_found_at', 'initial_max_glucose_value')->toArray(),
                    $this->cleanDocument($dto->document)
                )
                ->addRole();
            
        });

        return $this->user;
    }

    private function store(StorePatientDto $dto): self
    {
        $this->user = User::create([
            ...$dto->only('name', 'email')->toArray(),
            'password' => Hash::make(Str::random(12)),
        ]);

        return $this;
    }

    private function storePatient(array $patientData, string $cleanedDocument): self
    {
        $this->user->patient()->create([
            ...$patientData,
            'document_type' => DocumentTypes::RUT->value,
            'document' => $cleanedDocument,
            'user_id' => $this->user->id,
        ]);

        return $this;
    }

    private function cleanDocument(string $document): string
    {
        return $this->sanitizeDocumentWithoutVerificator($document);
    }

    private function addRole(): void
    {
        $this->user->assignRole('Patient');
    }
}
