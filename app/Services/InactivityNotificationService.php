<?php

namespace App\Services;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InactivityNotificationService
{
    public function __construct(
        private readonly MagicLinkService $magicLinkService
    ) {}

    /**
     * Query students who are registered, haven't completed the route, and have had no movement for >= 8 days.
     *
     * @return Collection<int, Student>
     */
    public function getEligibleInactiveStudents(int $days = 8): Collection
    {
        $cutoff = now()->subDays($days);

        return Student::query()
            ->with('user')
            ->where(function (Builder $query): void {
                $query->where('registration_status', 'approved')
                    ->orWhereNotNull('user_id');
            })
            ->where('completed_stations', '<', 12)
            ->where(function (Builder $query) use ($cutoff): void {
                $query->where(function (Builder $q) use ($cutoff): void {
                    $q->whereNotNull('last_active_at')
                        ->where('last_active_at', '<=', $cutoff);
                })->orWhere(function (Builder $q) use ($cutoff): void {
                    $q->whereNull('last_active_at')
                        ->where('created_at', '<=', $cutoff);
                });
            })
            ->where(function (Builder $query) use ($cutoff): void {
                $query->whereNull('last_inactivity_notified_at')
                    ->orWhere('last_inactivity_notified_at', '<=', $cutoff);
            })
            ->get();
    }

    /**
     * Notify an individual inactive student via GoHighLevel webhook.
     *
     * @return array{
     *     sent: bool,
     *     student_id: int,
     *     email: string
     * }
     */
    public function notifyStudent(Student $student, ?string $webhookUrl = null): array
    {
        $targetWebhook = $webhookUrl
            ?: config('services.gohighlevel.inactivity_webhook_url')
            ?: config('services.gohighlevel.webhook_url');

        $lastMovement = $student->last_active_at ?? $student->created_at ?? now();
        $daysInactive = max(8, (int) Carbon::parse($lastMovement)->diffInDays(now()));

        $stationIndex = (int) ($student->current_station ?: 1);
        $stationTitle = config("route.titles.{$stationIndex}", 'Campamento base');

        // If student has a User, generate a magic login link for instant resumption
        $resumeUrl = url('/');
        $magicLinkUrl = null;

        if ($student->user) {
            $tokenData = $this->magicLinkService->generateToken($student->user, 72);
            $resumeUrl = $tokenData['url'];
            $magicLinkUrl = $tokenData['url'];
        }

        $html = $this->buildInactivityEmailHtml($student, $stationTitle, $daysInactive, $resumeUrl);

        $payload = [
            'event' => 'vcp_student_inactive_reminder',
            'email' => $student->email,
            'name' => $student->full_name ?? $student->user?->name ?? 'Estudiante',
            'days_inactive' => $daysInactive,
            'current_station_number' => $stationIndex,
            'current_station_name' => $stationTitle,
            'completed_stations' => (int) ($student->completed_stations ?? 0),
            'completed_tasks' => (int) ($student->completed_tasks ?? 0),
            'progress_percentage' => (int) ($student->progress_percentage ?? 0),
            'system' => $student->system ?? 'medium',
            'residence' => $student->residence ?? 'usa',
            'route_url' => $resumeUrl,
            'magic_link_url' => $magicLinkUrl,
            'html' => $html,
        ];

        $sent = false;

        if (filled($targetWebhook)) {
            try {
                $response = $this->client()->post($targetWebhook, $payload);
                $sent = $response->successful();

                if (! $sent) {
                    Log::warning('GoHighLevel inactivity reminder webhook returned non-200', [
                        'student_id' => $student->id,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('GoHighLevel inactivity reminder webhook exception', [
                    'student_id' => $student->id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $student->forceFill(['last_inactivity_notified_at' => now()])->save();

        return [
            'sent' => $sent,
            'student_id' => $student->id,
            'email' => $student->email,
        ];
    }

    /**
     * Process all eligible inactive students.
     *
     * @return array{
     *     total: int,
     *     notified: int,
     *     skipped: int
     * }
     */
    public function processInactiveStudents(int $days = 8, ?string $webhookUrl = null): array
    {
        $students = $this->getEligibleInactiveStudents($days);
        $notified = 0;

        foreach ($students as $student) {
            $result = $this->notifyStudent($student, $webhookUrl);
            if ($result['sent']) {
                $notified++;
            }
        }

        return [
            'total' => $students->count(),
            'notified' => $notified,
            'skipped' => $students->count() - $notified,
        ];
    }

    /**
     * Build rich, responsive HTML ready for GHL email insertion.
     */
    public function buildInactivityEmailHtml(Student $student, string $stationTitle, int $daysInactive, string $resumeUrl): string
    {
        $name = htmlspecialchars($student->full_name ?? $student->user?->name ?? 'Estudiante', ENT_QUOTES, 'UTF-8');
        $safeStation = htmlspecialchars($stationTitle, ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($resumeUrl, ENT_QUOTES, 'UTF-8');
        $completedStations = (int) ($student->completed_stations ?? 0);
        $percentage = (int) ($student->progress_percentage ?? 0);

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>¡Te extrañamos en la Ruta del Éxito!</title>
</head>
<body style="margin:0;padding:0;background-color:#0d1117;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#e6edf3;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#0d1117;padding:30px 15px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:580px;background-color:#161b22;border:1px solid #30363d;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.5);">
          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#0f766e,#0284c7);padding:26px 32px;text-align:center;">
              <span style="display:inline-block;font-size:12px;letter-spacing:2px;font-weight:700;color:#ccfbf1;text-transform:uppercase;">Academia VendeComoPro</span>
              <h1 style="margin:8px 0 0;font-size:24px;font-weight:800;color:#ffffff;line-height:1.2;">¡Tu Expedición en Amazon te Espera!</h1>
            </td>
          </tr>
          <!-- Body Content -->
          <tr>
            <td style="padding:32px;font-size:16px;line-height:1.6;color:#c9d1d9;">
              <p style="margin:0 0 16px;font-size:18px;font-weight:600;color:#f0f6fc;">¡Hola, {$name}!</p>
              <p style="margin:0 0 18px;">
                Notamos que han pasado <strong>{$daysInactive} días</strong> desde tu última actividad en tu <strong>Ruta del Éxito</strong>.
              </p>
              <p style="margin:0 0 24px;color:#8b949e;">
                Recuerda: <em>tu meta no es verlo todo con prisa, es avanzar por logros reales</em>. Dedicarle hoy solo 15 minutos a tu siguiente misión puede desbloquear tu primera venta.
              </p>

              <!-- Progress Summary Box -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#0d1117;border:1px solid #30363d;border-radius:8px;margin-bottom:28px;">
                <tr>
                  <td style="padding:20px;">
                    <div style="font-size:12px;font-weight:700;color:#38bdf8;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Dónde te quedaste</div>
                    <div style="font-size:18px;font-weight:700;color:#ffffff;margin-bottom:12px;">Estación {$student->current_station}: {$safeStation}</div>
                    
                    <div style="background-color:#21262d;border-radius:6px;height:10px;overflow:hidden;margin-bottom:10px;">
                      <div style="background:linear-gradient(90deg,#0284c7,#38bdf8);height:10px;width:{$percentage}%;border-radius:6px;"></div>
                    </div>
                    
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                      <tr>
                        <td style="font-size:13px;color:#8b949e;">
                          Estaciones completadas: <strong style="color:#e6edf3;">{$completedStations} de 12</strong>
                        </td>
                        <td align="right" style="font-size:13px;color:#38bdf8;font-weight:700;">
                          {$percentage}% completado
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- CTA Button -->
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 28px;">
                <tr>
                  <td align="center" style="border-radius:8px;background:linear-gradient(135deg,#38bdf8,#0284c7);">
                    <a href="{$safeUrl}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:16px 36px;font-size:16px;font-weight:700;color:#0b1120;text-decoration:none;border-radius:8px;letter-spacing:0.3px;">
                      Continuar mi Ruta Ahora &rarr;
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 12px;font-size:14px;color:#8b949e;">
                &iquest;Tienes alguna duda o te trabaste en alguna estación? Recuerda que tus coaches están disponibles en <strong>Discord</strong> y en las <strong>clases en vivo</strong> para ayudarte.
              </p>
            </td>
          </tr>
          <!-- Footer -->
          <tr>
            <td style="border-top:1px solid #30363d;padding:20px 32px;background-color:#0d1117;font-size:12px;color:#6e7681;text-align:center;line-height:1.5;">
              <p style="margin:0 0 4px;"><strong>VendeComoPro</strong> &middot; Tu camino hacia una tienda rentable en Amazon.</p>
              <p style="margin:0;">Recibiste esta notificación porque estás inscrito en la Ruta del Éxito.</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->connectTimeout(5)->timeout(15)->retry(3, 250);
    }
}
