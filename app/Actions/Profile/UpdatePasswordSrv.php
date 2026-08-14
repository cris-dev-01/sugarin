<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\DataTransferObjects\Profile\UpdatePasswordDto;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdatePasswordSrv
{
    use AsAction;

    /**
     * @throws ValidationException
     */
    public function handle(UpdatePasswordDto $dto): User
    {
        $user = $this->getUser($dto->id);

        if (! Hash::check($dto->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'La clave actual ingresada no es correcta.',
            ]);
        }

        $user->update(['password' => Hash::make($dto->password)]);

        return $user;
    }

    private function getUser(int $id): User|null
    {
        return User::findOrFail($id);
    }
}
