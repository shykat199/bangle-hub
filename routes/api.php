<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CallWebhookController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// The ManyDial delivery hook (POST /api/webhook/manydial) is registered in
// routes/web.php — a duplicate here only overrode it with the same handler.
