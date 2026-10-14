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
        $messageText = $this->buildInactivityTextMessage($student, $stationTitle, $daysInactive, $resumeUrl);

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
            'message_headline' => '¡Tu Expedición en Amazon te Espera!',
            'message_text' => $messageText,
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
        $stationIndex = (int) ($student->current_station ?: 1);

        // Logo URL: direct hosted CDN asset
        $logoUrl = 'https://vcp-ruta-del-exito.vercel.app/assets/vcp-logo.png';

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>¡Tu Expedición en Amazon te Espera!</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f5f7;font-family:Montserrat,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;-webkit-font-smoothing:antialiased;color:#1e293b;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f4f5f7;padding:36px 16px;">
    <tr>
      <td align="center">
        <!-- Main Card Container (White) -->
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:580px;background-color:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;box-shadow:0 8px 30px rgba(0,0,0,0.06);">
          
          <!-- Brand Header with Logo on White/Clean Background -->
          <tr>
            <td align="center" style="padding:32px 24px 22px;background-color:#ffffff;border-top:4px solid #ff8500;border-bottom:1px solid #f1f5f9;">
              <a href="{$safeUrl}" target="_blank" rel="noopener noreferrer" style="text-decoration:none;display:inline-block;">
                <img src="{$logoUrl}" alt="VendeComoPro" width="140" style="display:block;margin:0 auto;width:140px;max-width:140px;height:auto;border:0;outline:none;text-decoration:none;" border="0">
              </a>
              <div style="margin-top:10px;font-size:11px;font-weight:800;color:#64748b;letter-spacing:2px;text-transform:uppercase;">
                ACADEMIA &middot; RUTA DEL &Eacute;XITO
              </div>
            </td>
          </tr>

          <!-- Banner Title -->
          <tr>
            <td style="background-color:#fff7ed;padding:22px 30px;text-align:center;border-bottom:1px solid #fed7aa;">
              <span style="display:inline-block;font-size:11px;letter-spacing:1.5px;font-weight:800;color:#c2410c;text-transform:uppercase;margin-bottom:6px;">
                AVANZA POR LOGROS, NO POR PRISA
              </span>
              <h1 style="margin:0;font-size:23px;font-weight:900;color:#0f172a;line-height:1.25;font-family:'Arial Black',Arial,sans-serif;letter-spacing:-0.4px;">
                ¡Tu Expedición en Amazon te Espera!
              </h1>
            </td>
          </tr>

          <!-- Body Content -->
          <tr>
            <td style="padding:32px 32px 24px;font-size:15px;line-height:1.65;color:#334155;">
              <p style="margin:0 0 16px;font-size:18px;font-weight:700;color:#0f172a;">
                ¡Hola, {$name}!
              </p>
              
              <p style="margin:0 0 16px;color:#334155;">
                Notamos que han pasado <strong style="color:#ea580c;font-weight:700;">{$daysInactive} días</strong> desde tu última actividad en tu <strong>Ruta del Éxito</strong>.
              </p>

              <!-- Quote Box -->
              <p style="margin:0 0 26px;color:#475569;font-size:14px;border-left:4px solid #ff8500;padding-left:16px;background:#f8fafc;padding-top:12px;padding-bottom:12px;border-radius:0 8px 8px 0;line-height:1.6;">
                <strong style="color:#0f172a;">Recuerda:</strong> <em>Tu meta no es verlo todo con prisa, es avanzar por logros reales.</em> Dedicarle hoy solo 15 minutos a tu siguiente misión puede desbloquear tu próxima victoria.
              </p>

              <!-- Progress Summary Box (White/Soft Border) -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#fafafa;border:1px solid #e5e7eb;border-radius:12px;margin-bottom:28px;">
                <tr>
                  <td style="padding:22px;">
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin-bottom:8px;">
                      <tr>
                        <td>
                          <span style="font-size:11px;font-weight:800;color:#ea580c;text-transform:uppercase;letter-spacing:1px;display:inline-block;">DÓNDE TE QUEDASTE</span>
                        </td>
                        <td align="right">
                          <span style="font-size:13px;font-weight:800;color:#ea580c;">{$percentage}% completado</span>
                        </td>
                      </tr>
                    </table>

                    <div style="font-size:18px;font-weight:800;color:#0f172a;margin-bottom:14px;line-height:1.3;">
                      Estación {$stationIndex}: {$safeStation}
                    </div>
                    
                    <!-- Progress Bar Track -->
                    <div style="background-color:#e2e8f0;border-radius:10px;height:12px;overflow:hidden;margin-bottom:12px;">
                      <div style="background:linear-gradient(90deg,#ff8500,#ea580c);height:12px;width:{$percentage}%;border-radius:10px;"></div>
                    </div>
                    
                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                      <tr>
                        <td style="font-size:13px;color:#64748b;">
                          Progreso del ciclo: <strong style="color:#0f172a;">{$completedStations} de 12 estaciones</strong>
                        </td>
                        <td align="right" style="font-size:13px;color:#64748b;">
                          Meta: Primera venta & utilidad
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <!-- CTA Button -->
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 28px;width:100%;">
                <tr>
                  <td align="center">
                    <a href="{$safeUrl}" target="_blank" rel="noopener noreferrer" style="display:inline-block;width:82%;max-width:380px;background:linear-gradient(135deg,#ff8500 0%,#ea580c 100%);color:#ffffff;font-size:16px;font-weight:800;text-decoration:none;text-align:center;padding:16px 24px;border-radius:10px;letter-spacing:0.3px;box-shadow:0 4px 14px rgba(234,88,12,0.35);">
                      Continuar mi Ruta Ahora &rarr;
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Support Note -->
              <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border-top:1px solid #e2e8f0;padding-top:18px;">
                <tr>
                  <td style="font-size:13px;color:#64748b;line-height:1.6;">
                    ¿Tienes alguna duda o te trabaste en tu estación? Recuerda que tus coaches están disponibles en <strong>Discord</strong> y en las <strong>clases grupales en vivo</strong> para resolverlo contigo.
                  </td>
                </tr>
              </table>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="border-top:1px solid #e2e8f0;padding:22px 28px;background-color:#f8fafc;font-size:12px;color:#64748b;text-align:center;line-height:1.6;">
              <p style="margin:0 0 6px;color:#334155;font-weight:700;letter-spacing:0.5px;">
                VENDECOMOPRO &middot; AVANZA POR LOGROS, NO POR PRISA.
              </p>
              <p style="margin:0 0 4px;">Tu camino hacia una tienda sólida y rentable en Amazon.</p>
              <p style="margin:0;color:#94a3b8;font-size:11px;">Recibiste esta notificación porque estás inscrito en la Ruta del Éxito.</p>
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

    /**
     * Build clean dynamic text copy ready to insert into SMS, WhatsApp or custom email template.
     */
    public function buildInactivityTextMessage(Student $student, string $stationTitle, int $daysInactive, string $resumeUrl): string
    {
        $name = $student->full_name ?? $student->user?->name ?? 'Estudiante';
        $stationIndex = (int) ($student->current_station ?: 1);
        $percentage = (int) ($student->progress_percentage ?? 0);

        return "¡Hola {$name}! Notamos que han pasado {$daysInactive} días desde tu última actividad en tu Ruta del Éxito. Te quedaste en la Estación {$stationIndex}: \"{$stationTitle}\" ({$percentage}% completado).\n\nRecuerda: tu meta no es verlo todo con prisa, sino dar un paso real cada día. Dedícale hoy solo 15 minutos a tu siguiente misión para seguir avanzando.\n\nHaz clic aquí para retomar tu ruta de inmediato:\n{$resumeUrl}";
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->connectTimeout(5)->timeout(15)->retry(3, 250);
    }
}
