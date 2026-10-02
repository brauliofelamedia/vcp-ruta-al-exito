<?php

namespace App\Console\Commands;

use App\Services\InactivityNotificationService;
use Illuminate\Console\Command;

class NotifyInactiveStudentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vcp:notify-inactive-students
                            {--days=8 : Minimum days of inactivity to trigger reminder}
                            {--webhook= : Optional webhook URL override}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check registered students inactive for 8+ days and dispatch reminder webhook to GoHighLevel with HTML email';

    /**
     * Execute the console command.
     */
    public function handle(InactivityNotificationService $service): int
    {
        $days = (int) $this->option('days');
        $webhook = $this->option('webhook');

        $this->info("Buscando estudiantes inactivos por {$days} días o más...");

        $results = $service->processInactiveStudents($days, $webhook);

        $this->info('Proceso completado:');
        $this->line("- Estudiantes encontrados: {$results['total']}");
        $this->line("- Notificaciones enviadas: {$results['notified']}");
        $this->line("- Omitidos o pendientes: {$results['skipped']}");

        return Command::SUCCESS;
    }
}
