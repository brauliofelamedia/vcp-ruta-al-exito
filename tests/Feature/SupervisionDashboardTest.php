<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SupervisionDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.supervision.token', 'test-supervision-token-12345');
    }

    public function test_rejects_unauthorized_access_without_token(): void
    {
        $response = $this->get('/supervision');

        $response->assertStatus(403);
    }

    public function test_rejects_invalid_token(): void
    {
        $response = $this->get('/supervision?token=invalid-token');

        $response->assertStatus(403);
    }

    public function test_allows_access_with_valid_token(): void
    {
        $student = Student::create([
            'email' => 'supervision.test@vendecomopro.net',
            'full_name' => 'Estudiante Supervisado',
            'system' => 'elite',
            'residence' => 'usa',
            'current_station' => 4,
            'completed_stations' => 3,
            'completed_tasks' => 12,
            'progress_percentage' => 35,
            'last_active_at' => now()->subDay(),
        ]);

        $response = $this->get('/supervision?token=test-supervision-token-12345');

        $response->assertStatus(200);
        $response->assertSee('Estudiante Supervisado');
        $response->assertSee('supervision.test@vendecomopro.net');
    }

    public function test_can_view_student_details_json(): void
    {
        $user = User::factory()->create([
            'email' => 'ana@example.com',
            'name' => 'Ana Gomez',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'full_name' => $user->name,
            'system' => 'medium',
            'residence' => 'outside',
            'current_station' => 2,
            'completed_stations' => 1,
            'completed_tasks' => 4,
            'progress_percentage' => 15,
            'last_active_at' => now()->subHours(2),
        ]);

        $student->progressEntries()->create([
            'task_key' => 'medium:0:academia',
            'completed' => true,
            'completed_at' => now(),
        ]);

        $response = $this->withSession(['supervision_authenticated_token' => 'test-supervision-token-12345'])
            ->getJson("/supervision/student/{$student->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'email' => 'ana@example.com',
                'full_name' => 'Ana Gomez',
            ],
            'completed_tasks' => ['medium:0:academia'],
        ]);
    }

    public function test_can_generate_magic_link_for_student(): void
    {
        $student = Student::create([
            'email' => 'juan@example.com',
            'full_name' => 'Juan Perez',
            'system' => 'medium',
            'residence' => 'usa',
        ]);

        $response = $this->withSession(['supervision_authenticated_token' => 'test-supervision-token-12345'])
            ->getJson("/supervision/magic-link/{$student->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'magic_url',
            'expires_at',
        ]);
    }

    public function test_can_export_csv(): void
    {
        Student::create([
            'email' => 'export@example.com',
            'full_name' => 'Alumno Exportable',
            'system' => 'medium',
            'residence' => 'usa',
            'current_station' => 1,
            'progress_percentage' => 10,
        ]);

        $response = $this->withSession(['supervision_authenticated_token' => 'test-supervision-token-12345'])
            ->get('/supervision/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
