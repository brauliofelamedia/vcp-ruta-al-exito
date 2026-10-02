<?php

namespace App\Services;

use App\Models\MagicLoginToken;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MagicLinkService
{
    /**
     * @return array{
     *     rawToken: string,
     *     tokenModel: MagicLoginToken,
     *     url: string,
     *     expiresAt: CarbonInterface
     * }
     */
    public function generateToken(User $user, int $hours = 24): array
    {
        $rawToken = Str::random(64);
        $hashed = hash('sha256', $rawToken);
        $expiresAt = now()->addHours($hours);

        // Invalidate previous unused tokens for this user
        MagicLoginToken::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        /** @var MagicLoginToken $tokenModel */
        $tokenModel = MagicLoginToken::query()->create([
            'user_id' => $user->id,
            'token' => $hashed,
            'expires_at' => $expiresAt,
        ]);

        $url = url('/auth/magic-login?token='.$rawToken.'&email='.urlencode($user->email));

        return [
            'rawToken' => $rawToken,
            'tokenModel' => $tokenModel,
            'url' => $url,
            'expiresAt' => $expiresAt,
        ];
    }

    /**
     * Send magic link payload to GoHighLevel webhook.
     *
     * @return array{
     *     sent: bool,
     *     url: string,
     *     rawToken: string
     * }
     */
    public function sendMagicLink(User $user, ?string $webhookUrl = null): array
    {
        $tokenData = $this->generateToken($user);
        $url = $tokenData['url'];
        $rawToken = $tokenData['rawToken'];
        $html = $this->buildEmailHtml($user->name, $url);

        $targetWebhook = $webhookUrl
            ?: config('services.gohighlevel.magic_link_webhook_url')
            ?: config('services.gohighlevel.webhook_url');

        $sent = false;

        $payload = [
            'event' => 'vcp_magic_link_requested',
            'email' => $user->email,
            'name' => $user->name,
            'magic_link_url' => $url,
            'expires_at' => $tokenData['expiresAt']->toIso8601String(),
            'html' => $html,
        ];

        if (filled($targetWebhook)) {
            try {
                $response = $this->client()->post($targetWebhook, $payload);
                $sent = $response->successful();

                if (! $sent) {
                    Log::warning('GoHighLevel magic link webhook returned non-200', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('GoHighLevel magic link webhook exception', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return [
            'sent' => $sent,
            'url' => $url,
            'rawToken' => $rawToken,
        ];
    }

    /**
     * Verify token and mark it as consumed.
     */
    public function verifyAndConsumeToken(string $email, string $rawToken): ?User
    {
        $user = User::query()->where('email', mb_strtolower(trim($email)))->first();

        if ($user === null) {
            return null;
        }

        $hashed = hash('sha256', trim($rawToken));

        /** @var MagicLoginToken|null $tokenRecord */
        $tokenRecord = MagicLoginToken::query()
            ->where('user_id', $user->id)
            ->where('token', $hashed)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($tokenRecord === null) {
            return null;
        }

        $tokenRecord->update(['used_at' => now()]);

        if ($user->student) {
            $user->student->update(['last_active_at' => now()]);
        }

        return $user;
    }

    /**
     * Build responsive HTML ready for GHL email templates.
     */
    public function buildEmailHtml(string $name, string $magicLinkUrl, int $hours = 24): string
    {
        $safeName = htmlspecialchars($name ?: 'Estudiante', ENT_QUOTES, 'UTF-8');
        $safeUrl = htmlspecialchars($magicLinkUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso a tu Ruta del Éxito</title>
</head>
<body style="margin:0;padding:0;background-color:#0d1117;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#e6edf3;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#0d1117;padding:30px 15px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:580px;background-color:#161b22;border:1px solid #30363d;border-radius:12px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.5);">
          <!-- Header -->
          <tr>
            <td style="background:linear-gradient(135deg,#0369a1,#0284c7);padding:24px 32px;text-align:center;">
              <span style="display:inline-block;font-size:12px;letter-spacing:2px;font-weight:700;color:#e0f2fe;text-transform:uppercase;">Academia VendeComoPro</span>
              <h1 style="margin:8px 0 0;font-size:24px;font-weight:800;color:#ffffff;line-height:1.2;">Tu Enlace de Acceso a la Ruta del Éxito</h1>
            </td>
          </tr>
          <!-- Body Content -->
          <tr>
            <td style="padding:32px;font-size:16px;line-height:1.6;color:#c9d1d9;">
              <p style="margin:0 0 16px;font-size:18px;font-weight:600;color:#f0f6fc;">¡Hola, {$safeName}!</p>
              <p style="margin:0 0 20px;">Has solicitado un enlace directo para ingresar a tu cuenta de la <strong>Ruta del Éxito en Amazon</strong>.</p>
              <p style="margin:0 0 28px;">Haz clic en el siguiente botón para iniciar sesión automáticamente en este dispositivo sin necesidad de recordar una contraseña:</p>

              <!-- CTA Button -->
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 28px;">
                <tr>
                  <td align="center" style="border-radius:8px;background:linear-gradient(135deg,#38bdf8,#0284c7);">
                    <a href="{$safeUrl}" target="_blank" rel="noopener noreferrer" style="display:inline-block;padding:16px 36px;font-size:16px;font-weight:700;color:#0b1120;text-decoration:none;border-radius:8px;letter-spacing:0.3px;">
                      Ingresar a mi Ruta del Éxito &rarr;
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Expiration note -->
              <div style="background-color:#0d1117;border-left:4px solid #38bdf8;padding:12px 16px;border-radius:4px;margin-bottom:24px;font-size:14px;color:#8b949e;">
                <strong style="color:#e6edf3;">Nota de seguridad:</strong> Este enlace es personal, de un solo uso y caducará en <strong>{$hours} horas</strong>.
              </div>

              <!-- Fallback text link -->
              <p style="margin:0 0 8px;font-size:13px;color:#8b949e;">Si el botón no funciona, copia y pega este enlace en tu navegador:</p>
              <p style="margin:0;font-size:12px;line-height:1.4;word-break:break-all;">
                <a href="{$safeUrl}" style="color:#38bdf8;text-decoration:underline;">{$safeUrl}</a>
              </p>
            </td>
          </tr>
          <!-- Footer -->
          <tr>
            <td style="border-top:1px solid #30363d;padding:20px 32px;background-color:#0d1117;font-size:12px;color:#6e7681;text-align:center;line-height:1.5;">
              <p style="margin:0 0 4px;"><strong>VendeComoPro</strong> &middot; Avanza por logros, no por prisa.</p>
              <p style="margin:0;">Si no solicitaste este acceso, puedes ignorar este correo con total tranquilidad.</p>
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
