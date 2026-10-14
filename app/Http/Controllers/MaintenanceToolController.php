<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceToolController extends Controller
{
    /**
     * Clear caches and optimize Laravel on hosting environments without SSH/terminal.
     */
    public function optimize(Request $request): JsonResponse
    {
        $expectedSecret = (string) (
            config('services.cron.secret')
            ?: env('CRON_SECRET')
            ?: env('APP_KEY')
        );

        $providedToken = (string) (
            $request->header('X-Cron-Secret')
            ?? $request->bearerToken()
            ?? $request->input('token')
        );

        // Security check
        if (blank($expectedSecret) || ! hash_equals($expectedSecret, $providedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing security token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $action = $request->input('action', 'clear');
        $output = [];

        try {
            if ($action === 'clear') {
                Artisan::call('route:clear');
                $output['route_clear'] = trim(Artisan::output());

                Artisan::call('config:clear');
                $output['config_clear'] = trim(Artisan::output());

                Artisan::call('cache:clear');
                $output['cache_clear'] = trim(Artisan::output());

                Artisan::call('view:clear');
                $output['view_clear'] = trim(Artisan::output());
            } elseif ($action === 'optimize') {
                Artisan::call('optimize:clear');
                $output['optimize_clear'] = trim(Artisan::output());

                Artisan::call('optimize');
                $output['optimize'] = trim(Artisan::output());
            } else {
                Artisan::call('optimize:clear');
                $output['optimize_clear'] = trim(Artisan::output());
            }

            return response()->json([
                'success' => true,
                'message' => 'Caches procesadas exitosamente.',
                'action' => $action,
                'details' => $output,
            ], Response::HTTP_OK);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ejecutar comandos: '.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
