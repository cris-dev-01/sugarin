<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\AuthenticateSrv;
use App\DataTransferObjects\Auth\AuthenticateDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    public function login(
        LoginRequest $request,
        AuthenticateSrv $authenticateSrv
    ): RedirectResponse
    {
        $authenticateSrv->handle(
            AuthenticateDto::from([
                ...$request->validated()
            ])
        );
        $request->session()->regenerate();

        return auth()->user()->hasRole('Administrator') ?
            redirect()->intended('/') :
            redirect()->to('/summary');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
