<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica el limitador `register` a la ruta de registro de Fortify.
 *
 * Fortify solo permite configurar el limitador de inicio de sesión, así que el de registro
 * se aplica aquí (middleware del grupo `web`) únicamente a `POST /register`.
 */
class ThrottleRegistration
{
    public function __construct(private readonly ThrottleRequests $throttle) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST') || ! $request->routeIs('register.store')) {
            return $next($request);
        }

        return $this->throttle->handle($request, $next, 'register');
    }
}
