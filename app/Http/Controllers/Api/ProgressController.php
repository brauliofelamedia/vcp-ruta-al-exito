<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentProgressResource;
use App\Jobs\SyncStudentProgressToGoHighLevel;
use App\Models\User;
use App\Services\GoHighLevelService;
use App\Services\ProgressService;
use App\Services\RouteProgressCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    public function show(Request $request, ProgressService $progressService): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $student = $progressService->findForUser($user);

        if ($student === null) {
            return response()->json(['ok' => true, 'exists' => false, 'student' => null, 'checks' => []]);
        }

        $resource = (new StudentProgressResource($student))->resolve($request);

        return response()->json([
            'ok' => true,
            'exists' => true,
            'student' => collect($resource)->except('checks')->all(),
            'checks' => $resource['checks'],
        ]);
    }

    public function store(Request $request, ProgressService $progressService, RouteProgressCalculator $routeProgressCalculator, GoHighLevelService $goHighLevelService): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'system' => ['nullable', 'in:medium,elite'],
            'residence' => ['nullable', 'in:usa,outside'],
            'taskKey' => ['nullable', 'string', 'max:255'],
            'completed' => ['nullable', 'boolean'],
            'stats' => ['nullable', 'array'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $student = $progressService->save($user, [
            'name' => $validated['name'] ?? null,
            'system' => $validated['system'] ?? 'medium',
            'residence' => $validated['residence'] ?? 'usa',
            'taskKey' => $validated['taskKey'] ?? null,
            'completed' => $validated['completed'] ?? false,
        ], $routeProgressCalculator);

        if ($goHighLevelService->isConfigured()) {
            $syncStats = array_merge($validated['stats'] ?? [], [
                'completedStations' => $student->completed_stations,
                'completedTasks' => $student->completed_tasks,
                'percentage' => $student->progress_percentage,
            ]);

            SyncStudentProgressToGoHighLevel::dispatch(
                $student->id,
                $syncStats,
                $validated['taskKey'] ?? '',
            )->afterCommit();
        }

        $resource = (new StudentProgressResource($student))->resolve($request);

        return response()->json([
            'ok' => true,
            'studentId' => $student->id,
            'saved' => true,
            'progress' => $resource['progress'],
        ]);
    }
}
