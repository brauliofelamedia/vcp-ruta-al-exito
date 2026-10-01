<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);
        $email = mb_strtolower($validated['email']);
        $student = Student::query()->firstOrNew(['email' => $email]);

        if ($student->exists && $student->user_id !== null) {
            return response()->json(['message' => 'Este correo ya tiene una cuenta. Inicia sesión para continuar.'], 409);
        }

        $student->fill([
            'full_name' => trim($validated['name']),
            'registration_status' => 'pending_ghl_validation',
            'last_active_at' => now(),
        ])->save();

        return response()->json([
            'ok' => true,
            'status' => $student->registration_status,
            'message' => 'Solicitud guardada. Validaremos tu acceso antes de habilitar el registro.',
        ], 202);
    }
}
