<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Services\MagicLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserProvisionController extends Controller
{
    /**
     * Endpoint to provision/register a user directly (e.g. from GoHighLevel, Zapier, Webhook, Admin).
     */
    public function store(Request $request, MagicLinkService $magicLinkService): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'system' => ['nullable', 'in:medium,elite'],
            'residence' => ['nullable', 'in:usa,outside'],
            'send_magic_link' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
        ]);

        $email = mb_strtolower(trim($validated['email']));
        $name = trim($validated['name']);

        // Find or create User
        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $name;

        $isNewUser = ! $user->exists;
        if ($isNewUser) {
            $user->password = Hash::make(Str::random(32));
        }
        $user->save();

        // Find or create Student
        $student = Student::query()->firstOrNew(['email' => $email]);
        $student->user()->associate($user);
        $student->full_name = $name;
        $student->registration_status = 'approved';

        if (! empty($validated['system'])) {
            $student->system = $validated['system'];
        }
        if (! empty($validated['residence'])) {
            $student->residence = $validated['residence'];
        }
        if (! $student->exists) {
            $student->last_active_at = now();
        }
        $student->save();

        $magicLinkSent = false;
        if (! empty($validated['send_magic_link'])) {
            $result = $magicLinkService->sendMagicLink($user);
            $magicLinkSent = $result['sent'];
        }

        return response()->json([
            'ok' => true,
            'message' => 'Usuario dado de alta exitosamente.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'student' => [
                'id' => $student->id,
                'system' => $student->system,
                'residence' => $student->residence,
                'registration_status' => $student->registration_status,
            ],
            'magic_link_sent' => $magicLinkSent,
        ], $isNewUser ? 201 : 200);
    }
}
