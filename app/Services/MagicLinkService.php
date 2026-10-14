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
        $messageText = $this->buildTextMessage($user->name, $url);

        $payload = [
            'event' => 'vcp_magic_link_requested',
            'email' => $user->email,
            'name' => $user->name,
            'magic_link_url' => $url,
            'route_url' => $url,
            'expires_at' => $tokenData['expiresAt']->toIso8601String(),
            'message_headline' => 'Tu Enlace de Acceso a la Ruta del Éxito',
            'message_text' => $messageText,
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
        $logoUrl = 'https://rutadelexito.vendecomopro.net/assets/vcp-logo.png?v=2';

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceso a tu Ruta del Éxito</title>
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
                ACCESO DIRECTO SIN CONTRASEÑA
              </span>
              <h1 style="margin:0;font-size:23px;font-weight:900;color:#0f172a;line-height:1.25;font-family:'Arial Black',Arial,sans-serif;letter-spacing:-0.4px;">
                Tu Enlace de Acceso a la Ruta del Éxito
              </h1>
            </td>
          </tr>

          <!-- Body Content -->
          <tr>
            <td style="padding:32px 32px 24px;font-size:15px;line-height:1.65;color:#334155;">
              <p style="margin:0 0 16px;font-size:18px;font-weight:700;color:#0f172a;">
                ¡Hola, {$safeName}!
              </p>
              
              <p style="margin:0 0 20px;color:#334155;">
                Has solicitado un enlace directo para ingresar a tu cuenta de la <strong>Ruta del Éxito en Amazon</strong>.
              </p>

              <p style="margin:0 0 28px;color:#475569;">
                Haz clic en el siguiente botón para iniciar sesión automáticamente en este dispositivo sin necesidad de escribir contraseñas:
              </p>

              <!-- CTA Button -->
              <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 auto 28px;width:100%;">
                <tr>
                  <td align="center">
                    <a href="{$safeUrl}" target="_blank" rel="noopener noreferrer" style="display:inline-block;width:82%;max-width:380px;background:linear-gradient(135deg,#ff8500 0%,#ea580c 100%);color:#ffffff;font-size:16px;font-weight:800;text-decoration:none;text-align:center;padding:16px 24px;border-radius:10px;letter-spacing:0.3px;box-shadow:0 4px 14px rgba(234,88,12,0.35);">
                      Ingresar a mi Ruta del Éxito &rarr;
                    </a>
                  </td>
                </tr>
              </table>

              <!-- Security Note -->
              <div style="background-color:#f8fafc;border-left:4px solid #ff8500;padding:14px 16px;border-radius:0 8px 8px 0;margin-bottom:24px;font-size:14px;color:#475569;line-height:1.6;">
                <strong style="color:#0f172a;">Nota de seguridad:</strong> Este enlace es personal, seguro y caducará automáticamente en <strong>{$hours} horas</strong>.
              </div>

              <!-- Fallback Text Link -->
              <div style="border-top:1px solid #e2e8f0;padding-top:18px;margin-top:18px;">
                <p style="margin:0 0 8px;font-size:13px;color:#64748b;">Si el botón no abre directamente, copia y pega este enlace en tu navegador:</p>
                <p style="margin:0;font-size:12px;line-height:1.5;word-break:break-all;">
                  <a href="{$safeUrl}" style="color:#ea580c;text-decoration:underline;">{$safeUrl}</a>
                </p>
              </div>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="border-top:1px solid #e2e8f0;padding:22px 28px;background-color:#f8fafc;font-size:12px;color:#64748b;text-align:center;line-height:1.6;">
              <p style="margin:0 0 6px;color:#334155;font-weight:700;letter-spacing:0.5px;">
                VENDECOMOPRO &middot; AVANZA POR LOGROS, NO POR PRISA.
              </p>
              <p style="margin:0 0 4px;">Tu camino hacia una tienda sólida y rentable en Amazon.</p>
              <p style="margin:0;color:#94a3b8;font-size:11px;">Si no solicitaste este acceso, puedes ignorar este correo con total tranquilidad.</p>
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
     * Build clean dynamic text copy ready to insert into SMS or WhatsApp template.
     */
    public function buildTextMessage(string $name, string $magicLinkUrl): string
    {
        $safeName = $name ?: 'Estudiante';

        return "¡Hola {$safeName}! Aquí tienes tu enlace de acceso directo a la Ruta del Éxito en Amazon:\n\n{$magicLinkUrl}\n\nEste enlace es seguro, personal y caduca en 24 horas.";
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()->connectTimeout(5)->timeout(15)->retry(3, 250);
    }
}
