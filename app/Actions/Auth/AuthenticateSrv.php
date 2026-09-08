<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\DataTransferObjects\Auth\AuthenticateDto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

class AuthenticateSrv
{
    use AsAction;

    /**
     * @throws Throwable
     */
    public function handle(AuthenticateDto $dto): self
    {
        if (! Auth::attempt($dto->toArray())) {
            throw ValidationException::withMessages([
                'password' => 'La clave ingresada no es correcta.',
            ]);
        }

        return $this;
    }
}
