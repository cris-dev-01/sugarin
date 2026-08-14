<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\DataTransferObjects\Profile\UpdateProfileDto;
use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateProfileSrv
{
    use AsAction;

    public function handle(UpdateProfileDto $dto): User
    {
        $user = User::findOrFail($dto->id);
        $user->update($dto->only('name', 'email')->toArray());

        return $user;
    }
}
