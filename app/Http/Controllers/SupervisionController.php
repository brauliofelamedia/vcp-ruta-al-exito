<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Services\MagicLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupervisionController extends Controller
{
    /**
     * Display the supervision overview dashboard.
     */
    public function index(Request $request): View
    {
        $stationTitles = config('route.titles', []);

        // Base query with relationships
        $query = Student::query()->with('user');

        // Search filter
        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('ghl_contact_id', 'like', "%{$search}%");
            });
        }

        // Station filter
        if ($request->filled('station') && $request->input('station') !== 'all') {
            $query->where('current_station', (int) $request->input('station'));
        }

        // System filter
        if ($request->filled('system') && $request->input('system') !== 'all') {
            $query->where('system', $request->input('system'));
        }

        // Residence filter
        if ($request->filled('residence') && $request->input('residence') !== 'all') {
            $query->where('residence', $request->input('residence'));
        }

        // Status / Inactivity quick tab filter
        $now = now();
        $statusTab = $request->input('status_tab', 'all');

        if ($statusTab === 'active_recent') {
            $query->where('last_active_at', '>=', $now->copy()->subDays(2));
        } elseif ($statusTab === 'warning') {
            $query->where('last_active_at', '<', $now->copy()->subDays(2))
                ->where('last_active_at', '>=', $now->copy()->subDays(7));
        } elseif ($statusTab === 'critical') {
            $query->where(function ($q) use ($now): void {
                $q->where('last_active_at', '<', $now->copy()->subDays(7))
                    ->orWhereNull('last_active_at');
            });
        } elseif ($statusTab === 'completed') {
            $query->where('progress_percentage', '>=', 100);
        }

        // Ordering
        $sortBy = (string) $request->input('sort', 'last_active_at');
        $direction = strtolower((string) $request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

        if (in_array($sortBy, ['full_name', 'email', 'progress_percentage', 'current_station', 'last_active_at', 'created_at'], true)) {
            $query->orderBy($sortBy, $direction);
        } else {
            $query->orderByDesc('last_active_at');
        }

        $students = $query->paginate(25)->withQueryString();

        // Global Metrics Calculation (unfiltered for top KPIs)
        $totalStudents = Student::query()->count();
        $averageProgress = (int) round(Student::query()->avg('progress_percentage') ?? 0);
        $completedStudents = Student::query()->where('progress_percentage', '>=', 100)->count();

        $activeRecentCount = Student::query()->where('last_active_at', '>=', $now->copy()->subDays(2))->count();
        $warningCount = Student::query()->where('last_active_at', '<', $now->copy()->subDays(2))
            ->where('last_active_at', '>=', $now->copy()->subDays(7))->count();
        $criticalInactiveCount = Student::query()->where(function ($q) use ($now): void {
            $q->where('last_active_at', '<', $now->copy()->subDays(7))
                ->orWhereNull('last_active_at');
        })->count();

        $usaCount = Student::query()->where('residence', 'usa')->count();
        $outsideCount = Student::query()->where('residence', 'outside')->count();

        // Breakdown per station for chart
        $stationCountsRaw = Student::query()
            ->selectRaw('current_station, count(*) as total')
            ->groupBy('current_station')
            ->pluck('total', 'current_station')
            ->all();

        $stationChartData = [];
        $stationChartLabels = [];
        for ($i = 1; $i <= 12; $i++) {
            $stationChartLabels[] = "E{$i}: ".($stationTitles[$i] ?? "Estación {$i}");
            $stationChartData[] = $stationCountsRaw[$i] ?? 0;
        }

        // Progress distribution brackets
        $progressBrackets = [
            '0-25%' => Student::query()->whereBetween('progress_percentage', [0, 25])->count(),
            '26-50%' => Student::query()->whereBetween('progress_percentage', [26, 50])->count(),
            '51-75%' => Student::query()->whereBetween('progress_percentage', [51, 75])->count(),
            '76-100%' => Student::query()->whereBetween('progress_percentage', [76, 100])->count(),
        ];

        return view('supervision.index', [
            'students' => $students,
            'stationTitles' => $stationTitles,
            'kpis' => [
                'total' => $totalStudents,
                'avg_progress' => $averageProgress,
                'completed' => $completedStudents,
                'active_recent' => $activeRecentCount,
                'warning' => $warningCount,
                'critical_inactive' => $criticalInactiveCount,
                'usa' => $usaCount,
                'outside' => $outsideCount,
            ],
            'charts' => [
                'stations' => [
                    'labels' => $stationChartLabels,
                    'data' => $stationChartData,
                ],
                'activity' => [
                    'labels' => ['Activos (≤48h)', 'Inactivos (3-7d)', 'Críticos (>7d)'],
                    'data' => [$activeRecentCount, $warningCount, $criticalInactiveCount],
                ],
                'progress' => [
                    'labels' => array_keys($progressBrackets),
                    'data' => array_values($progressBrackets),
                ],
                'systems' => [
                    'labels' => ['Medium', 'Élite'],
                    'data' => [
                        Student::query()->where('system', 'medium')->count(),
                        Student::query()->where('system', 'elite')->count(),
                    ],
                ],
            ],
            'filters' => [
                'search' => $request->input('search'),
                'station' => $request->input('station'),
                'system' => $request->input('system'),
                'residence' => $request->input('residence'),
                'status_tab' => $statusTab,
                'sort' => $sortBy,
                'direction' => $direction,
            ],
            'currentToken' => (string) ($request->query('token') ?? session('supervision_authenticated_token')),
        ]);
    }

    /**
     * Detailed view / inspect progress entries of a student.
     */
    public function showStudent(int $id): JsonResponse
    {
        $student = Student::query()->with(['user', 'progressEntries'])->findOrFail($id);
        $stationTitles = config('route.titles', []);

        $completedTaskKeys = $student->progressEntries
            ->where('completed', true)
            ->pluck('task_key')
            ->all();

        return response()->json([
            'success' => true,
            'student' => [
                'id' => $student->id,
                'full_name' => $student->full_name ?? 'Sin nombre',
                'email' => $student->email,
                'system' => strtoupper($student->system ?? 'medium'),
                'residence' => $student->residence === 'usa' ? 'USA' : 'Fuera de USA',
                'current_station' => $student->current_station ?? 1,
                'current_station_title' => $stationTitles[$student->current_station ?? 1] ?? 'Estación 1',
                'completed_stations' => $student->completed_stations ?? 0,
                'completed_tasks' => $student->completed_tasks ?? 0,
                'progress_percentage' => $student->progress_percentage ?? 0,
                'last_active_at' => $student->last_active_at ? $student->last_active_at->diffForHumans() : 'Nunca',
                'last_active_at_full' => $student->last_active_at ? $student->last_active_at->format('d/m/Y H:i:s') : 'Nunca',
                'created_at' => $student->created_at ? $student->created_at->format('d/m/Y') : '-',
                'ghl_contact_id' => $student->ghl_contact_id,
            ],
            'completed_tasks' => $completedTaskKeys,
            'station_titles' => $stationTitles,
        ]);
    }

    /**
     * Generate an instant Magic Login Link for an authorized student to share directly.
     */
    public function generateMagicLink(int $id, MagicLinkService $magicLinkService): JsonResponse
    {
        $student = Student::query()->with('user')->findOrFail($id);

        $user = $student->user;
        if (! $user) {
            $user = User::query()->where('email', mb_strtolower($student->email))->first();
            if (! $user) {
                $user = User::create([
                    'name' => $student->full_name ?? 'Estudiante',
                    'email' => mb_strtolower($student->email),
                    'password' => bcrypt(str()->random(32)),
                ]);
            }
            $student->user()->associate($user);
            $student->save();
        }

        $linkData = $magicLinkService->generateToken($user, 72); // 72 hours valid

        return response()->json([
            'success' => true,
            'magic_url' => $linkData['url'],
            'expires_at' => $linkData['expiresAt']->diffForHumans(),
        ]);
    }

    /**
     * Export all students or filtered list to CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $stationTitles = config('route.titles', []);
        $students = Student::query()->orderByDesc('last_active_at')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="reporte_estudiantes_vcp_'.now()->format('Y-m-d_His').'.csv"',
        ];

        return response()->stream(function () use ($students, $stationTitles): void {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Nombre',
                'Email',
                'Sistema',
                'Residencia',
                'Estación Actual',
                'Nombre Estación',
                'Estaciones Completadas',
                'Tareas Completadas',
                'Progreso (%)',
                'Última Actividad',
                'GHL Contact ID',
                'Fecha Registro',
            ]);

            foreach ($students as $s) {
                fputcsv($handle, [
                    $s->id,
                    $s->full_name ?? 'N/A',
                    $s->email,
                    strtoupper($s->system ?? 'medium'),
                    $s->residence === 'usa' ? 'USA' : 'Fuera de USA',
                    $s->current_station ?? 1,
                    $stationTitles[$s->current_station ?? 1] ?? '',
                    $s->completed_stations ?? 0,
                    $s->completed_tasks ?? 0,
                    $s->progress_percentage ?? 0,
                    $s->last_active_at ? $s->last_active_at->format('Y-m-d H:i:s') : 'Nunca',
                    $s->ghl_contact_id ?? '',
                    $s->created_at ? $s->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
