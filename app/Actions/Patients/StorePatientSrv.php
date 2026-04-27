<?php

declare(strict_types=1);

namespace App\Actions\Patients;

use App\DataTransferObjects\Patients\StorePatientDto;
use App\Enums\DocumentTypes;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class StorePatientSrv
{
    use AsAction;

    private User $user;

    /**
     * @throws Throwable
     */
    public function handle(StorePatientDto $dto): User|Model
    {
        DB::transaction(function () use ($dto) {
            $this->store($dto)
                ->storePatient($dto->only(['glucose_range_id', 'document', 'illness_found_at', 'initial_max_glucose_value'])->toArray());
            
        });

        return $this->user;
    }

    private function store(StorePatientDto $dto): void
    {
        $this->user = User::create([
            ...$dto->only(['name', 'email'])->toArray(),
            'password' => Hash::make(Str::random(12)),
        ]);
    }

    private function storePatient(array $patientData): void
    {
        $this->user->patient()->create([
            ...$patientData,
            'document_type' => DocumentTypes::RUT->value,
        ]);
    }
}
