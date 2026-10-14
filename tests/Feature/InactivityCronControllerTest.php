<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InactivityCronControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_request_when_cron_secret_is_not_configured(): void
    {
        Config::set('services.cron.secret', null);

        $response = $this->getJson('/api/v1/cron/notify-inactive?token=any-token');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'CRON_SECRET is not configured on the server.',
            ]);
    }

    public function test_rejects_unauthorized_token(): void
    {
        Config::set('services.cron.secret', 'valid-secret-token');

        $response = $this->getJson('/api/v1/cron/notify-inactive?token=wrong-token');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthorized: Invalid cron token.',
            ]);
    }

    public function test_accepts_valid_token_and_processes_inactivity(): void
    {
        Config::set('services.cron.secret', 'super-secret-cron-key');
        Config::set('services.gohighlevel.inactivity_webhook_url', 'https://example.com/ghl-inactivity-webhook');

        Http::fake([
            'https://example.com/ghl-inactivity-webhook' => Http::response(['status' => 'success'], 200),
        ]);

        $user = User::factory()->create([
            'email' => 'carlos@example.com',
            'name' => 'Carlos Mendoza',
        ]);

        Student::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'full_name' => $user->name,
            'registration_status' => 'approved',
            'completed_stations' => 2,
            'current_station' => 3,
            'progress_percentage' => 25,
            'last_active_at' => now()->subDays(10),
            'last_inactivity_notified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/cron/notify-inactive', [
            'token' => 'super-secret-cron-key',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Inactivity notifications processed successfully.',
                'summary' => [
                    'total' => 1,
                    'notified' => 1,
                    'skipped' => 0,
                ],
            ]);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $data['email'] === 'carlos@example.com'
                && $data['current_station_number'] === 3
                && isset($data['message_text'])
                && str_contains($data['message_text'], 'Carlos Mendoza')
                && str_contains($data['message_text'], 'Estación 3')
                && isset($data['message_headline'])
                && isset($data['html']);
        });
    }

    public function test_accepts_token_via_header(): void
    {
        Config::set('services.cron.secret', 'secret-via-header');
        Config::set('services.gohighlevel.inactivity_webhook_url', null);

        $response = $this->withHeader('X-Cron-Secret', 'secret-via-header')
            ->getJson('/api/v1/cron/notify-inactive');

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);
    }
}
