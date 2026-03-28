<?php

use Illuminate\Support\Facades\Route;
use Vital\Airwallex\Http\Controllers\WebhookController;

Route::post(config('lunar.airwallex.webhook_path', 'airwallex/webhook'), WebhookController::class)
    ->middleware(['api'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->name('lunar.airwallex.webhook');
