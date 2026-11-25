<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email')->toString())->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Este utilizador está inactivo.'],
            ]);
        }

        return response()->json([
            ...$this->issueTokens($user),
            'user' => new UserResource($user),
        ]);
    }

    public function refresh(Request $request): JsonResponse
    {
        $token = $this->tokenFromRequest($request, 'refresh');

        if (! $token || ($token->expires_at && $token->expires_at->isPast())) {
            return response()->json(['message' => 'Token de renovação inválido ou expirado.'], 401);
        }

        $user = $token->user;

        if (! $user || ! $user->is_active) {
            return response()->json(['message' => 'Utilizador inactivo.'], 401);
        }

        // Rotação do token de renovação.
        $token->delete();

        return response()->json($this->issueTokens($user));
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user instanceof User) {
            $user->tokens()->delete();
        } else {
            $this->tokenFromRequest($request, 'access')?->delete();
        }

        return response()->json(['message' => 'Sessão terminada.']);
    }

    public function profile(Request $request): UserResource
    {
        /** @var User $user */
        $user = Auth::user();

        return new UserResource($user);
    }

    public function changePassword(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $data = [
            'oldPassword' => $request->input('oldPassword', $request->input('current_password')),
            'newPassword' => $request->input('newPassword', $request->input('password')),
        ];

        Validator::make($data, [
            'oldPassword' => 'required|string',
            'newPassword' => 'required|string|min:6',
        ])->validate();

        if (! Hash::check($data['oldPassword'], $user->password)) {
            throw ValidationException::withMessages([
                'oldPassword' => ['A palavra-passe actual está incorrecta.'],
            ]);
        }

        $user->forceFill(['password' => Hash::make($data['newPassword'])])->save();

        return response()->json(['message' => 'Palavra-passe alterada com sucesso.']);
    }

    /** @return array{access_token: string, refresh_token: string} */
    private function issueTokens(User $user): array
    {
        return [
            'access_token' => ApiToken::issue($user, 'access', 60),
            'refresh_token' => ApiToken::issue($user, 'refresh', 60 * 24 * 7),
        ];
    }

    private function tokenFromRequest(Request $request, string $name): ?ApiToken
    {
        $plainToken = $request->bearerToken();

        if (! $plainToken) {
            return null;
        }

        return ApiToken::query()
            ->where('token_hash', hash('sha256', $plainToken))
            ->where('name', $name)
            ->first();
    }
}
