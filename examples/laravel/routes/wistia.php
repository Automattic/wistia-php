<?php
declare(strict_types=1);

use App\Http\Controllers\WistiaWebhookController;
use Illuminate\Support\Facades\Route;

// Registered outside the session-based "web" group, not a global CSRF bypass.
// HMAC verification in the controller is mandatory on this POST-only route.
Route::post('/webhooks/wistia', WistiaWebhookController::class)->name('wistia.webhook');
