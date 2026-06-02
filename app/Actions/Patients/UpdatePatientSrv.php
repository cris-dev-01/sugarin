<?php

declare(strict_types=1);

namespace App\Actions\Patients;

use App\DataTransferObjects\Patients\UpdatePatientDto;
use App\Models\User;
use App\Traits\FormatsDocument;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class UpdatePatientSrv
{
    use AsAction, FormatsDocument;

    private User $user;

    /**
     * @throws Throwable
     */
    public function handle(UpdatePatientDto $dto): User|Model
    {
        DB::transaction(function () use ($dto) {
            $this->getPatient($dto->id)
                ->update($dto)
                ->updatePatient(
                    $dto->only('glucose_range_id', 'illness_found_at', 'initial_max_glucose_value')->toArray(),
                    $this->cleanDocument($dto->document)
                );
            
        });

        return $this->user;
    }

    private function getPatient(int $id): self
    {
        $this->user = User::with('patient')->findOrFail($id);

        return $this;
    }

    private function update(UpdatePatientDto $dto): self
    {
        $this->user->update($dto->only('name', 'email')->toArray());

        return $this;
    }

    private function updatePatient(array $patientData, string $cleanedDocument): self
    {
        $this->user->patient()->update([
            ...$patientData,
            'document' => $cleanedDocument
        ]);

        return $this;
    }

    private function cleanDocument(string $document): string
    {
        return $this->sanitizeDocumentWithoutVerificator($document);
    }
}
