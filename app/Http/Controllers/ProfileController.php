<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Profile\UpdatePasswordSrv;
use App\Actions\Profile\UpdateProfileSrv;
use App\DataTransferObjects\Profile\UpdatePasswordDto;
use App\DataTransferObjects\Profile\UpdateProfileDto;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Profile/Index', [
            'patient' => auth()->user()->patient,
        ]);
    }

    public function update(UpdateProfileRequest $request, UpdateProfileSrv $updateSrv): RedirectResponse
    {
        $user = $updateSrv->handle(
            UpdateProfileDto::from([
                ...$request->validated(),
                'id' => $request->user()->id,
            ])
        );

        return redirect()->route('profile.index')->with('response', $user);
    }

    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordSrv $updateSrv): RedirectResponse
    {
        $updateSrv->handle(
            UpdatePasswordDto::from([
                ...$request->validated(),
                'id' => $request->user()->id,
            ])
        );

        return redirect()->route('profile.index')->with('success', 'Clave actualizada correctamente.');
    }
}
