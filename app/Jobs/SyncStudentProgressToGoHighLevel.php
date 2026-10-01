<?php

namespace App\Jobs;

use App\Models\Student;
use App\Services\GoHighLevelService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncStudentProgressToGoHighLevel implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    /** @param array<string, mixed> $stats */
    public function __construct(
        public int $studentId,
        public array $stats,
        public string $lastTaskKey,
    ) {}

    public function handle(GoHighLevelService $goHighLevelService): void
    {
        $student = Student::query()->findOrFail($this->studentId);

        $goHighLevelService->sync($student, $this->stats, $this->lastTaskKey);
    }
}
