<?php

namespace App\Console\Commands;

use App\Services\GoHighLevelService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

#[Signature('ghl:test {email?}')]
#[Description('Prueba de conexion con la API de GoHighLevel')]
class TestGhlConnection extends Command
{
    public function handle(GoHighLevelService $ghl): int
    {
        $this->info('Verificando credenciales configuradas...');
        $locationId = (string) config('services.gohighlevel.location_id');
        $apiKey = (string) config('services.gohighlevel.api_key');

        $this->line("Location ID: {$locationId}");
        $this->line('API Token presente: '.(filled($apiKey) ? 'SI ('.strlen($apiKey).' caracteres)' : 'NO'));

        if (! $ghl->hasApiCredentials()) {
            $this->error('Faltan credenciales en el archivo .env');

            return Command::FAILURE;
        }

        $this->info('Consultando endpoint de contactos de GHL...');
        try {
            $response = Http::withToken($apiKey)
                ->withHeaders(['Version' => '2021-07-28'])
                ->acceptJson()
                ->get('https://services.leadconnectorhq.com/contacts/', [
                    'locationId' => $locationId,
                    'limit' => 2,
                ]);

            $this->line("HTTP Status: {$response->status()}");

            if ($response->successful()) {
                $this->info('¡Conexion exitosa con GoHighLevel!');
                $contacts = $response->json('contacts') ?? [];
                $total = $response->json('total') ?? count($contacts);
                $this->line("Total contactos consultables: {$total}");

                $email = $this->argument('email');
                if ($email) {
                    $this->info("Buscando contacto especifico: {$email}");
                    $found = $ghl->findContactByEmail($email);
                    if ($found) {
                        $this->info("Contacto encontrado: {$found['id']}");
                        $this->line('Nombre: '.($found['name'] ?? $found['contactName'] ?? 'N/A'));
                        $tags = $found['tags'] ?? [];
                        $this->line('Tags: '.implode(', ', $tags));
                        $system = $ghl->determineSystemFromTags($tags);
                        $this->line('Sistema detectado: '.($system ?? 'Ninguno'));
                    } else {
                        $this->warn('No se encontro ningun contacto con ese correo en este Location.');
                    }
                }

                return Command::SUCCESS;
            } else {
                $this->error('Error de respuesta desde GHL:');
                $this->line($response->body());

                return Command::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error("Excepcion al conectar con GHL: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
