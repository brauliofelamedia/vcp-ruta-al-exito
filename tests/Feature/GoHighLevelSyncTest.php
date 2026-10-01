<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Services\GoHighLevelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoHighLevelSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_progress_to_the_configured_webhook(): void
    {
        config(['services.gohighlevel.webhook_url' => 'https://ghl.test/webhook']);
        Http::fake(['https://ghl.test/webhook' => Http::response([], 200)]);

        $student = Student::query()->create([
            'email' => 'student@gmail.com',
            'full_name' => 'Estudiante VCP',
            'system' => 'elite',
            'residence' => 'usa',
            'last_active_at' => now(),
        ]);

        app(GoHighLevelService::class)->sync($student, [
            'completedStations' => 3,
            'completedTasks' => 12,
            'percentage' => 25,
            'currentStationName' => 'Aprender a analizar',
        ], 'elite:3:practice');

        Http::assertSent(fn ($request): bool => $request->url() === 'https://ghl.test/webhook'
            && $request['email'] === 'student@gmail.com'
            && $request['completedStations'] === 3);
    }
}
