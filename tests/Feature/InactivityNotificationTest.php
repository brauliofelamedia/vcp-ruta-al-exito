<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use App\Services\InactivityNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InactivityNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_identifies_and_notifies_inactive_students_after_eight_days(): void
    {
        config(['services.gohighlevel.inactivity_webhook_url' => 'https://ghl.test/inactivity-webhook']);
        Http::fake(['https://ghl.test/inactivity-webhook' => Http::response([], 200)]);

        $user = User::query()->create([
            'name' => 'Pedro Dormido',
            'email' => 'pedro@dormido.com',
            'password' => 'secret',
        ]);

        $inactiveStudent = Student::query()->create([
            'user_id' => $user->id,
            'email' => 'pedro@dormido.com',
            'full_name' => 'Pedro Dormido',
            'registration_status' => 'approved',
            'system' => 'medium',
            'residence' => 'usa',
            'current_station' => 3,
            'completed_stations' => 2,
            'completed_tasks' => 8,
            'progress_percentage' => 17,
            'last_active_at' => now()->subDays(9),
        ]);

        $activeStudent = Student::query()->create([
            'email' => 'activo@test.com',
            'full_name' => 'Activo Reciente',
            'registration_status' => 'approved',
            'system' => 'medium',
            'residence' => 'usa',
            'last_active_at' => now()->subDays(2),
        ]);

        $finishedStudent = Student::query()->create([
            'email' => 'graduado@test.com',
            'full_name' => 'Graduado VCP',
            'registration_status' => 'approved',
            'completed_stations' => 12,
            'last_active_at' => now()->subDays(15),
        ]);

        $service = app(InactivityNotificationService::class);
        $eligible = $service->getEligibleInactiveStudents(8);

        $this->assertCount(1, $eligible);
        $this->assertEquals($inactiveStudent->id, $eligible->first()->id);

        // Run the Artisan command
        $this->artisan('vcp:notify-inactive-students', ['--days' => 8])
            ->assertSuccessful();

        // Webhook should be sent for inactiveStudent only
        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://ghl.test/inactivity-webhook'
                && $request['event'] === 'vcp_student_inactive_reminder'
                && $request['email'] === 'pedro@dormido.com'
                && $request['name'] === 'Pedro Dormido'
                && $request['current_station_number'] === 3
                && $request['current_station_name'] === 'Apertura de cuenta Seller Central'
                && $request['days_inactive'] >= 8
                && str_contains($request['html'], '¡Tu Expedición en Amazon te Espera!')
                && str_contains($request['html'], 'Estación 3: Apertura de cuenta Seller Central')
                && str_contains($request['html'], 'Continuar mi Ruta Ahora');
        });

        // Ensure active student was not sent
        Http::assertNotSent(fn ($request): bool => $request['email'] === 'activo@test.com');
        Http::assertNotSent(fn ($request): bool => $request['email'] === 'graduado@test.com');

        // Check that last_inactivity_notified_at is now set
        $inactiveStudent->refresh();
        $this->assertNotNull($inactiveStudent->last_inactivity_notified_at);

        // Running again immediately should find 0 eligible students (won't re-notify until another 8 days)
        $this->assertCount(0, $service->getEligibleInactiveStudents(8));
    }
}
