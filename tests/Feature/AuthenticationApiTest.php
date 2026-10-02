<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MagicLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_is_disabled(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Estudiante Intruso',
            'email' => 'intruso@gmail.com',
            'password' => 'secure-password',
        ])->assertStatus(403)
            ->assertJsonPath('message', 'El registro público está deshabilitado. La cuenta debe ser dada de alta por la academia.');
    }

    public function test_it_provisions_a_user_and_approved_student_via_api(): void
    {
        $response = $this->postJson('/api/v1/users', [
            'name' => 'Braulio Perez',
            'email' => 'braulio@vendecomopro.com',
            'system' => 'elite',
            'residence' => 'usa',
        ])->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('user.name', 'Braulio Perez')
            ->assertJsonPath('user.email', 'braulio@vendecomopro.com')
            ->assertJsonPath('student.system', 'elite')
            ->assertJsonPath('student.registration_status', 'approved');

        $this->assertDatabaseHas('users', ['email' => 'braulio@vendecomopro.com', 'name' => 'Braulio Perez']);
        $this->assertDatabaseHas('students', [
            'email' => 'braulio@vendecomopro.com',
            'full_name' => 'Braulio Perez',
            'system' => 'elite',
            'registration_status' => 'approved',
        ]);
    }

    public function test_it_provisions_via_registration_requests_endpoint(): void
    {
        $this->postJson('/api/v1/registration-requests', [
            'name' => 'Maria Gonzalez',
            'email' => 'maria@vendecomopro.com',
        ])->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('user.name', 'Maria Gonzalez')
            ->assertJsonPath('student.registration_status', 'approved');

        $this->assertDatabaseHas('users', ['email' => 'maria@vendecomopro.com']);
    }

    public function test_it_returns_404_when_requesting_magic_link_for_unregistered_email(): void
    {
        $this->postJson('/api/v1/auth/magic-link', [
            'email' => 'desconocido@example.com',
        ])->assertStatus(404)
            ->assertJsonPath('message', 'No encontramos una cuenta registrada con este correo electrónico. Por favor verifica tu correo o contacta a soporte para darte de alta.');
    }

    public function test_it_requests_magic_link_and_sends_ghl_webhook_with_html(): void
    {
        config(['services.gohighlevel.magic_link_webhook_url' => 'https://ghl.test/magic-link-webhook']);
        Http::fake(['https://ghl.test/magic-link-webhook' => Http::response([], 200)]);

        // Provision user first
        $this->postJson('/api/v1/users', [
            'name' => 'Estudiante Activo',
            'email' => 'activo@vendecomopro.com',
        ])->assertCreated();

        $response = $this->postJson('/api/v1/auth/magic-link', [
            'email' => 'activo@vendecomopro.com',
        ])->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('sent', true);

        // Verify token created in database
        $this->assertDatabaseHas('magic_login_tokens', [
            'used_at' => null,
        ]);

        // Verify webhook sent with HTML field
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://ghl.test/magic-link-webhook'
                && $request['event'] === 'vcp_magic_link_requested'
                && $request['email'] === 'activo@vendecomopro.com'
                && $request['name'] === 'Estudiante Activo'
                && str_contains($request['magic_link_url'], '/auth/magic-login?token=')
                && str_contains($request['html'], 'Tu Enlace de Acceso a la Ruta del Éxito')
                && str_contains($request['html'], 'Ingresar a mi Ruta del Éxito');
        });
    }

    public function test_it_verifies_magic_link_and_redirects_with_auth_token(): void
    {
        $user = User::query()->create([
            'name' => 'Carlos Magic',
            'email' => 'carlos@magic.com',
            'password' => 'secret-hashed',
        ]);

        $tokenData = app(MagicLinkService::class)->generateToken($user);
        $rawToken = $tokenData['rawToken'];

        $response = $this->get('/auth/magic-login?token='.$rawToken.'&email='.urlencode($user->email));

        $response->assertRedirect();
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('auth_token=', $targetUrl);
        $this->assertStringContainsString('email=carlos%40magic.com', $targetUrl);

        // Token must now be marked as used
        $this->assertDatabaseMissing('magic_login_tokens', [
            'id' => $tokenData['tokenModel']->id,
            'used_at' => null,
        ]);

        // Second attempt with same token must fail
        $secondResponse = $this->get('/auth/magic-login?token='.$rawToken.'&email='.urlencode($user->email));
        $secondResponse->assertRedirect();
        $secondTarget = $secondResponse->headers->get('Location');
        $this->assertStringContainsString('auth_error=enlace_expirado', $secondTarget);
    }

    public function test_it_logs_out_an_authenticated_user(): void
    {
        $user = User::query()->create([
            'name' => 'Lucia Rios',
            'email' => 'lucia@example.com',
            'password' => 'secret123',
        ]);

        $token = $user->createToken('test', ['progress:read', 'progress:write'])->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('ok', true);
    }
}
