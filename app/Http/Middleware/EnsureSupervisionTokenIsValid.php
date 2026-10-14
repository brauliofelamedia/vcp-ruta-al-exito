<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSupervisionTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) (
            config('services.supervision.token')
            ?: config('services.cron.secret')
            ?: env('SUPERVISION_TOKEN')
            ?: env('CRON_SECRET')
        );

        if (blank($expectedToken)) {
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'El token de supervisión (SUPERVISION_TOKEN o CRON_SECRET) no está configurado en el servidor.');
        }

        $sessionKey = 'supervision_authenticated_token';
        $providedToken = (string) (
            $request->query('token')
            ?? $request->header('X-Supervision-Token')
            ?? $request->session()->get($sessionKey)
        );

        if (blank($providedToken) || ! hash_equals($expectedToken, $providedToken)) {
            abort(Response::HTTP_FORBIDDEN, 'Acceso denegado: Token de supervisión no proporcionado o inválido.');
        }

        // Store validated token in session for convenience when navigating filters/tabs
        if ($request->hasSession()) {
            $request->session()->put($sessionKey, $expectedToken);
        }

        return $next($request);
    }
}
