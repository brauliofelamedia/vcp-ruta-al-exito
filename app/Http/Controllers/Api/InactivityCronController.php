<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\InactivityNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InactivityCronController extends Controller
{
    /**
     * Trigger inactivity check externally (e.g. cron-job.org, EasyCron, external monitoring).
     */
    public function trigger(Request $request, InactivityNotificationService $service): JsonResponse
    {
        $expectedSecret = (string) config('services.cron.secret');

        if (blank($expectedSecret)) {
            return response()->json([
                'success' => false,
                'message' => 'CRON_SECRET is not configured on the server.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $providedToken = (string) (
            $request->header('X-Cron-Secret')
            ?? $request->bearerToken()
            ?? $request->input('token')
        );

        if (! hash_equals($expectedSecret, $providedToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid cron token.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $days = (int) $request->input('days', 8);
        $webhook = $request->input('webhook');

        $results = $service->processInactiveStudents(days: $days, webhookUrl: $webhook);

        return response()->json([
            'success' => true,
            'message' => 'Inactivity notifications processed successfully.',
            'days_threshold' => $days,
            'summary' => $results,
        ], Response::HTTP_OK);
    }
}
