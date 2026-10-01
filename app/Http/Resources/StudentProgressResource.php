<?php

namespace App\Http\Resources;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Student */
class StudentProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'fullName' => $this->full_name,
            'system' => $this->system,
            'residence' => $this->residence,
            'progress' => [
                'currentStation' => $this->current_station,
                'completedStations' => $this->completed_stations,
                'completedTasks' => $this->completed_tasks,
                'percentage' => $this->progress_percentage,
            ],
            'checks' => $this->progressEntries
                ->where('completed', true)
                ->mapWithKeys(fn ($progress): array => [$progress->task_key => true])
                ->all(),
        ];
    }
}
