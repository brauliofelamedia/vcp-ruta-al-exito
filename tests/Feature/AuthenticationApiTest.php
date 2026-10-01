<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_registers_a_student_and_returns_a_sanctum_token(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Estudiante VCP',
            'email' => 'student@gmail.com',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
            'device_name' => 'Expo',
        ])->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('user.email', 'student@gmail.com')
            ->assertJsonStructure(['token']);
    }

    public function test_it_registers_with_six_character_password_without_confirmation(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Ana Gomez',
            'email' => 'ana@example.com',
            'password' => '123456',
        ])->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('user.name', 'Ana Gomez')
            ->assertJsonPath('user.email', 'ana@example.com')
            ->assertJsonStructure(['token']);
    }

    public function test_it_logs_in_an_existing_user(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'Carlos Perez',
            'email' => 'carlos@example.com',
            'password' => 'clave123',
        ])->assertCreated();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'carlos@example.com',
            'password' => 'clave123',
        ])->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('user.name', 'Carlos Perez')
            ->assertJsonStructure(['token']);
    }

    public function test_it_logs_out_an_authenticated_user(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Lucia Rios',
            'email' => 'lucia@example.com',
            'password' => 'secret123',
        ])->assertCreated();

        $token = $response->json('token');

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk()
            ->assertJsonPath('ok', true);
    }
}
