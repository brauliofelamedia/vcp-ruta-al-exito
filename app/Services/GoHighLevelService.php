<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

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
            || (filled(config('services.gohighlevel.api_key')) && filled(config('services.gohighlevel.location_id')));
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->connectTimeout(5)->timeout(15)->retry(3, 250);
    }
}
