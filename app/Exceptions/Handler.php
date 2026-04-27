<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Manejar errores de autorización (403) en peticiones Inertia
        // Esto incluye errores desde FormRequest::authorize() y Gates/Policies
        $this->renderable(function (AuthorizationException $e, Request $request) {
            // Solo interceptar peticiones POST/PUT/PATCH/DELETE con header X-Inertia
            if ($request->header('X-Inertia') && 
                in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                
                // Retornar respuesta JSON simple que el cliente puede interceptar
                return response()->json([
                    'message' => $e->getMessage() ?: 'No tienes permisos para realizar esta acción.',
                ], 403);
            }
        });

        // Manejar HttpException 403 (abort(403))
        $this->renderable(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 403 && 
                $request->header('X-Inertia') && 
                in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                
                return response()->json([
                    'message' => $e->getMessage() ?: 'No tienes permisos para realizar esta acción.',
                ], 403);
            }
        });
    }
}
