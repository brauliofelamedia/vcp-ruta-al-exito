<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProgressService
{
    public function findForUser(User $user): ?Student
    {
        return $user->student()->with('progressEntries')->first();
    }

    /** @param array{name?: ?string, system: string, residence: string, taskKey?: ?string, completed?: bool} $attributes */
    public function save(User $user, array $attributes, RouteProgressCalculator $routeProgressCalculator): Student
    {
        return DB::transaction(function () use ($user, $attributes, $routeProgressCalculator): Student {
            $student = $user->student ?? Student::query()->firstOrNew(['email' => mb_strtolower($user->email)]);
            $student->user()->associate($user);
            $student->registration_status = 'approved';
            $student->fill(['system' => $attributes['system'], 'residence' => $attributes['residence'], 'last_active_at' => now()]);

            if (filled($attributes['name'])) {
                $student->full_name = trim($attributes['name']);
            }

            $student->save();

            $student->load('progressEntries');

            if (filled($attributes['taskKey'])) {
                $routeProgressCalculator->assertTaskCanBeUpdated($student, $attributes['taskKey'], $attributes['completed'] ?? false);
                $student->progressEntries()->updateOrCreate(
                    ['task_key' => $attributes['taskKey']],
                    ['completed' => $attributes['completed'] ?? false, 'completed_at' => now()],
                );
            }

            $student->load('progressEntries');
            $progress = $routeProgressCalculator->calculate($student);
            $student->fill([
                'current_station' => $progress['currentStation'],
                'completed_stations' => $progress['completedStations'],
                'completed_tasks' => $progress['completedTasks'],
                'progress_percentage' => $progress['percentage'],
            ])->save();

            return $student;
        });
    }
}
