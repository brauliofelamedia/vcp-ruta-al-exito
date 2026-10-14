<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GoHighLevelService;
use App\Services\MagicLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationRequestController extends Controller
{
    public function store(Request $request, UserProvisionController $provisionController, MagicLinkService $magicLinkService, GoHighLevelService $goHighLevelService): JsonResponse
    {
        return $provisionController->store($request, $magicLinkService, $goHighLevelService);
    }
}
