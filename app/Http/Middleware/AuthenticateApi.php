<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApi
{
    /**
     * Valida o token Bearer e autentica o utilizador da API.
     *
     * @param  Closure(Request): Response  $next
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if (! $plainToken) {
            throw new AuthenticationException('Não autenticado.');
        }

        $token = ApiToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->first();

        if (! $token || ($token->expires_at && $token->expires_at->isPast())) {
            throw new AuthenticationException('Token inválido ou expirado.');
        }

        $user = $token->user;

        if (! $user || ! $user->is_active) {
            throw new AuthenticationException('Utilizador inactivo.');
        }

        $token->forceFill(['last_used_at' => now()])->save();
        Auth::setUser($user);

        return $next($request);
    }
}
