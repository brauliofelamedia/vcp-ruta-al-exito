<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GoHighLevelService
{
    /** @param array<string, mixed> $stats */
    public function sync(Student $student, array $stats, string $lastTaskKey): void
    {
        $payload = [
            'email' => $student->email,
            'name' => $student->full_name ?? '',
            'system' => $student->system,
            'residence' => $student->residence,
            'completedStations' => $stats['completedStations'] ?? 0,
            'totalStations' => 12,
            'completedTasks' => $stats['completedTasks'] ?? 0,
            'progressPercentage' => $stats['percentage'] ?? 0,
            'currentStationName' => $stats['currentStationName'] ?? '',
            'currentMilestone' => $stats['currentMilestone'] ?? '',
            'lastTaskKey' => $lastTaskKey,
            'lastActiveAt' => $student->last_active_at?->toIso8601String(),
            'event' => 'vcp_route_progress_updated',
        ];

        if (filled(config('services.gohighlevel.webhook_url'))) {
            $this->client()->post(config('services.gohighlevel.webhook_url'), $payload)->throw();
        }

        if (filled(config('services.gohighlevel.api_key')) && filled(config('services.gohighlevel.location_id'))) {
            $this->client()
                ->withToken(config('services.gohighlevel.api_key'))
                ->withHeaders(['Version' => '2021-07-28'])
                ->post('https://services.leadconnectorhq.com/contacts/upsert', [
                    'email' => $student->email,
                    'name' => $student->full_name,
                    'locationId' => config('services.gohighlevel.location_id'),
                    'customFields' => [
                        ['key' => 'vcp_estacion_actual', 'value' => $payload['currentStationName']],
                        ['key' => 'vcp_estaciones_completas', 'value' => (string) $payload['completedStations']],
                        ['key' => 'vcp_progreso_porcentaje', 'value' => $payload['progressPercentage'].'%'],
                        ['key' => 'vcp_tareas_completadas', 'value' => (string) $payload['completedTasks']],
                        ['key' => 'vcp_sistema', 'value' => $student->system],
                        ['key' => 'vcp_residencia', 'value' => $student->residence],
                    ],
                    'tags' => ['vcp-sistema-'.$student->system, 'vcp-estacion-'.$payload['completedStations']],
                ])->throw();
        }
    }

    public function isConfigured(): bool
    {
        return filled(config('services.gohighlevel.webhook_url'))
            || $this->hasApiCredentials();
    }

    public function hasApiCredentials(): bool
    {
        return filled(config('services.gohighlevel.api_key'))
            && filled(config('services.gohighlevel.location_id'));
    }

    /**
     * Search contact by email in GoHighLevel API.
     *
     * @return array<string, mixed>|null
     */
    public function findContactByEmail(string $email): ?array
    {
        if (! $this->hasApiCredentials()) {
            return null;
        }

        $email = mb_strtolower(trim($email));
        $locationId = (string) config('services.gohighlevel.location_id');

        // 1. Try search/duplicate endpoint
        try {
            $response = $this->client()
                ->withToken(config('services.gohighlevel.api_key'))
                ->withHeaders(['Version' => '2021-07-28'])
                ->get('https://services.leadconnectorhq.com/contacts/search/duplicate', [
                    'locationId' => $locationId,
                    'email' => $email,
                ]);

            if ($response->successful()) {
                $contact = $response->json('contact');
                if (is_array($contact) && ! empty($contact['id'])) {
                    return $contact;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GoHighLevel search/duplicate failed: '.$e->getMessage());
        }

        // 2. Fallback to contacts search by query
        try {
            $response = $this->client()
                ->withToken(config('services.gohighlevel.api_key'))
                ->withHeaders(['Version' => '2021-07-28'])
                ->get('https://services.leadconnectorhq.com/contacts/', [
                    'locationId' => $locationId,
                    'query' => $email,
                ]);

            if ($response->successful()) {
                $contacts = $response->json('contacts') ?? [];
                foreach ($contacts as $c) {
                    if (isset($c['email']) && mb_strtolower(trim((string) $c['email'])) === $email) {
                        return $c;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GoHighLevel contacts query failed: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Determine system ('elite' or 'medium') based on GHL contact tags.
     *
     * @param  list<string>|array<string, mixed>  $tags
     */
    public function determineSystemFromTags(array $tags): ?string
    {
        $normalizedTags = array_map(
            fn ($t): string => mb_strtolower(trim((string) $t)),
            array_values($tags)
        );

        // High-ticket / elite takes precedence
        foreach ($normalizedTags as $tag) {
            if (in_array($tag, ['high-ticket', 'high ticket', 'highticket', 'elite'], true)) {
                return 'elite';
            }
        }

        // Medium system
        foreach ($normalizedTags as $tag) {
            if (in_array($tag, ['medium', 'medium ticket', 'medium-ticket', 'sistema medium'], true)) {
                return 'medium';
            }
        }

        return null;
    }

    /**
     * Extract contact name with fallbacks.
     *
     * @param  array<string, mixed>  $contact
     */
    public function extractContactName(array $contact, string $fallbackEmail): string
    {
        if (! empty($contact['name']) && is_string($contact['name'])) {
            return trim($contact['name']);
        }

        if (! empty($contact['contactName']) && is_string($contact['contactName'])) {
            return trim($contact['contactName']);
        }

        $firstName = is_string($contact['firstName'] ?? null) ? trim($contact['firstName']) : '';
        $lastName = is_string($contact['lastName'] ?? null) ? trim($contact['lastName']) : '';
        $combined = trim($firstName.' '.$lastName);

        if ($combined !== '') {
            return $combined;
        }

        $parts = explode('@', $fallbackEmail);

        return $parts[0] ?? 'Estudiante';
    }

    /**
     * Provision user and student from GHL contact data.
     *
     * @param  array<string, mixed>  $contact
     */
    public function provisionUserFromContact(array $contact, string $system): User
    {
        $email = mb_strtolower(trim((string) ($contact['email'] ?? '')));
        $name = $this->extractContactName($contact, $email);
        $country = is_string($contact['country'] ?? null) ? $contact['country'] : null;
        $residence = Student::determineResidence($country);

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->name = $name;

        if (! $user->exists) {
            $user->password = Hash::make(Str::random(32));
        }
        $user->save();

        $student = Student::query()->firstOrNew(['email' => $email]);
        $student->user()->associate($user);
        $student->full_name = $name;
        $student->system = $system;
        $student->residence = $residence;
        $student->registration_status = 'approved';
        $student->ghl_contact_id = isset($contact['id']) ? (string) $contact['id'] : null;

        $metadata = $student->metadata ?? [];
        if ($country) {
            $metadata['country'] = $country;
        }
        $metadata['ghl_tags'] = $contact['tags'] ?? [];
        $metadata['auto_provisioned_at'] = now()->toIso8601String();
        $student->metadata = $metadata;

        if (! $student->exists) {
            $student->last_active_at = now();
        }
        $student->save();

        return $user;
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->connectTimeout(5)->timeout(15)->retry(3, 250);
    }
}
