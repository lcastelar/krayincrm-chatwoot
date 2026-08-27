<?php

use Illuminate\Support\Facades\Route;
use Webkul\Chatwoot\Http\Controllers\ChatwootEmbedController;
use Webkul\Chatwoot\Http\Controllers\ChatwootWebhookController;

Route::group(['middleware' => ['web']], function () {
    Route::get('/chatwoot/embed', [ChatwootEmbedController::class, 'index'])->name('chatwoot.embed.index');
    Route::post('/chatwoot/embed/search', [ChatwootEmbedController::class, 'search'])->name('chatwoot.embed.search');
    Route::post('/chatwoot/embed/lead/store', [ChatwootEmbedController::class, 'storeLead'])->name('chatwoot.embed.lead.store');
    Route::post('/chatwoot/embed/lead/update-stage', [ChatwootEmbedController::class, 'updateStage'])->name('chatwoot.embed.lead.update-stage');
    Route::post('/chatwoot/embed/activity/store', [ChatwootEmbedController::class, 'storeActivity'])->name('chatwoot.embed.activity.store');
    Route::get('/chatwoot/debug-headers', function () {
        return response()->json([
            'headers' => request()->headers->all(),
            'server' => array_intersect_key($_SERVER, array_flip([
                'HTTP_X_FORWARDED_PROTO', 'HTTP_X_FORWARDED_PORT', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED_HOST',
                'HTTPS', 'SERVER_PORT', 'REQUEST_SCHEME', 'HTTP_X_FORWARDED_SSL'
            ])),
            'secure' => request()->secure(),
            'url' => url('/'),
        ]);
    });
});

Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {
    Route::post('/chatwoot/webhook', [ChatwootWebhookController::class, 'handle'])->name('chatwoot.webhook');
});
