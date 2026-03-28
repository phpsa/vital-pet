<?php

namespace Vital\Airwallex\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController
{
    public function __invoke(Request $request): JsonResponse
    {
        // Keep webhook endpoint available for future event handling.
        return response()->json(['ok' => true]);
    }
}
