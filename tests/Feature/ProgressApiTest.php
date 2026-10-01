<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProgressApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_and_reads_a_students_progress(): void
    {
        $user = User::factory()->create(['email' => 'student@gmail.com']);
        Sanctum::actingAs($user, ['progress:read', 'progress:write']);

        $this->postJson('/api/v1/me/progress', [
            'name' => 'Estudiante VCP',
            'system' => 'elite',
            'residence' => 'usa',
            'taskKey' => 'elite:0:academia',
            'completed' => true,
        ])->assertOk()->assertJsonPath('ok', true)->assertJsonPath('saved', true);

        $this->getJson('/api/v1/me/progress')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('exists', true)
            ->assertJsonPath('student.email', 'student@gmail.com')
            ->assertJsonPath('student.fullName', 'Estudiante VCP')
            ->assertJsonPath('checks.elite:0:academia', true);
    }

    public function test_it_returns_an_empty_progress_for_a_new_student(): void
    {
        Sanctum::actingAs(User::factory()->create(), ['progress:read']);

        $this->getJson('/api/v1/me/progress')
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('exists', false)
            ->assertJsonPath('checks', []);
    }

    public function test_it_does_not_expose_progress_without_a_token(): void
    {
        $this->getJson('/api/v1/me/progress')->assertUnauthorized();
    }

    public function test_it_blocks_tasks_from_future_stations_and_tracks_the_current_station(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['progress:read', 'progress:write']);

        $this->postJson('/api/v1/me/progress', [
            'system' => 'elite',
            'residence' => 'usa',
            'taskKey' => 'elite:2:modules',
            'completed' => true,
        ])->assertUnprocessable()->assertJsonValidationErrors('taskKey');

        foreach (['academia', 'discord', 'zoom', 'passwords'] as $taskId) {
            $response = $this->postJson('/api/v1/me/progress', [
                'system' => 'elite',
                'residence' => 'usa',
                'taskKey' => "elite:0:{$taskId}",
                'completed' => true,
            ])->assertOk();
        }

        $response->assertJsonPath('progress.currentStation', 2)
            ->assertJsonPath('progress.completedStations', 1)
            ->assertJsonPath('progress.completedTasks', 4);

        $this->getJson('/api/v1/me/progress')
            ->assertJsonPath('student.progress.currentStation', 2)
            ->assertJsonPath('student.progress.completedStations', 1);
    }
}
