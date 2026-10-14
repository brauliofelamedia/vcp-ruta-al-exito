<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GoHighLevelService;
use App\Services\MagicLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Public registration is disabled. Users are provisioned via administrative API / webhooks.
     */
    public function register(): JsonResponse
    {
        return response()->json([
            'message' => 'El registro público está deshabilitado. La cuenta debe ser dada de alta por la academia.',
        ], 403);
    }

    /**
     * Request a passwordless magic login link sent via GoHighLevel webhook,
     * and immediately authenticate the user if authorized (Option A).
     */
    public function sendMagicLink(Request $request, MagicLinkService $magicLinkService, GoHighLevelService $goHighLevelService): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $user = User::query()->where('email', $email)->first();
        $contact = null;

        // If user is not yet created locally, verify via GoHighLevel API
        if ($user === null) {
            if ($goHighLevelService->hasApiCredentials()) {
                $contact = $goHighLevelService->findContactByEmail($email);

                if ($contact !== null) {
                    $system = $goHighLevelService->determineSystemFromTags($contact['tags'] ?? []);

                    if ($system !== null) {
                        $user = $goHighLevelService->provisionUserFromContact($contact, $system);
                    } else {
                        return response()->json([
                            'message' => 'Tu correo fue localizado en el sistema pero no cuenta con un programa activo para ingresar a esta ruta. Por favor contacta a soporte para verificar tu acceso.',
                        ], 403);
                    }
                }
            }

            if ($user === null) {
                return response()->json([
                    'message' => 'No encontramos una cuenta registrada con este correo electrónico. Por favor verifica tu correo o contacta a soporte para darte de alta.',
                ], 404);
            }
        } else {
            // User already exists locally: ensure rutadelexito-website tag is in GHL if API credentials configured
            if ($goHighLevelService->hasApiCredentials()) {
                $student = $user->student;
                $contactId = $student?->ghl_contact_id;
                if (! empty($contactId)) {
                    $goHighLevelService->addTagToContact($contactId, 'rutadelexito-website');
                } else {
                    $contact = $goHighLevelService->findContactByEmail($email);
                    if ($contact !== null && ! empty($contact['id'])) {
                        if ($student) {
                            $student->ghl_contact_id = (string) $contact['id'];
                            $student->save();
                        }
                        $goHighLevelService->addTagToContact((string) $contact['id'], 'rutadelexito-website');
                    }
                }
            }
        }

        // Send magic link to user email (mandatory verification step)
        $result = $magicLinkService->sendMagicLink($user);

        return response()->json([
            'ok' => true,
            'message' => 'Hemos verificado tu cuenta. Te enviamos un enlace de acceso seguro a tu correo electrónico para que entres a la ruta.',
            'sent' => $result['sent'],
        ]);
    }

    /**
     * Handle magic link redirect and issue Sanctum token.
     */
    public function verifyMagicLink(Request $request, MagicLinkService $magicLinkService): RedirectResponse
    {
        $email = (string) $request->query('email', '');
        $token = (string) $request->query('token', '');

        if (blank($email) || blank($token)) {
            return redirect()->route('ruta', ['auth_error' => 'enlace_invalido']);
        }

        $user = $magicLinkService->verifyAndConsumeToken($email, $token);

        if ($user === null) {
            return redirect()->route('ruta', ['auth_error' => 'enlace_expirado']);
        }

        $sanctumToken = $user->createToken('magic-web', ['progress:read', 'progress:write'])->plainTextToken;

        return redirect()->route('ruta', [
            'auth_token' => $sanctumToken,
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $user = User::query()->where('email', mb_strtolower($validated['email']))->first();

        if ($user === null || ! Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Correo o contraseña incorrectos.'], 422);
        }

        return $this->tokenResponse($user, $validated['device_name'] ?? 'web');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['ok' => true]);
    }

    private function tokenResponse(User $user, string $deviceName): JsonResponse
    {
        $token = $user->createToken($deviceName, ['progress:read', 'progress:write'])->plainTextToken;

        return response()->json([
            'ok' => true,
            'token' => $token,
            'user' => ['name' => $user->name, 'email' => $user->email],
        ], 201);
    }
}
