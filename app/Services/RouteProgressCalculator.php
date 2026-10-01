<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Validation\ValidationException;

class RouteProgressCalculator
{
    /** @return array{currentStation: int, currentStationIndex: int, completedStations: int, completedTasks: int, totalTasks: int, percentage: int} */
    public function calculate(Student $student): array
    {
        $checks = $student->progressEntries
            ->where('completed', true)
            ->mapWithKeys(fn ($entry): array => [$entry->task_key => true])
            ->all();
        $completedStations = 0;
        $completedTasks = 0;
        $currentStationIndex = 11;
        $hasIncompleteStation = false;

        foreach ($this->stations($student->residence) as $index => $taskIds) {
            $completedForStation = 0;

            foreach ($taskIds as $taskId) {
                if ($checks[$this->taskKey($student->system, $index, $taskId)] ?? false) {
                    $completedForStation++;
                }
            }

            $completedTasks += $completedForStation;

            if ($completedForStation === count($taskIds)) {
                $completedStations++;

                continue;
            }

            if (! $hasIncompleteStation) {
                $currentStationIndex = $index;
                $hasIncompleteStation = true;
            }
        }

        $totalTasks = array_sum(array_map('count', $this->stations($student->residence)));

        return [
            'currentStation' => $currentStationIndex + 1,
            'currentStationIndex' => $currentStationIndex,
            'completedStations' => $completedStations,
            'completedTasks' => $completedTasks,
            'totalTasks' => $totalTasks,
            'percentage' => (int) round(($completedTasks / $totalTasks) * 100),
        ];
    }

    public function assertTaskCanBeUpdated(Student $student, string $taskKey, bool $completed): void
    {
        $parts = explode(':', $taskKey, 3);

        if (count($parts) !== 3 || $parts[0] !== $student->system || ! ctype_digit($parts[1])) {
            throw ValidationException::withMessages(['taskKey' => ['La tarea no pertenece a la ruta seleccionada.']]);
        }

        $stationIndex = (int) $parts[1];
        $taskIds = $this->stations($student->residence)[$stationIndex] ?? null;

        if ($taskIds === null || ! in_array($parts[2], $taskIds, true)) {
            throw ValidationException::withMessages(['taskKey' => ['La tarea no existe en la ruta del estudiante.']]);
        }

        if ($completed && $stationIndex > $this->calculate($student)['currentStationIndex']) {
            throw ValidationException::withMessages(['taskKey' => ['Completa primero las tareas de la estación actual para desbloquear la siguiente.']]);
        }
    }

    /** @return array<int, list<string>> */
    private function stations(string $residence): array
    {
        return array_map(
            fn (array $tasks): array => array_is_list($tasks) ? $tasks : $tasks[$residence],
            config('route.stations'),
        );
    }

    private function taskKey(string $system, int $stationIndex, string $taskId): string
    {
        return $system.':'.$stationIndex.':'.$taskId;
    }
}
