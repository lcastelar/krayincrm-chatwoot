<?php

use Illuminate\Support\Facades\Route;
use Webkul\Chatwoot\Http\Controllers\ChatwootAdminController;
use Webkul\Chatwoot\Http\Controllers\ChatwootEmbedController;
use Webkul\Chatwoot\Http\Controllers\ChatwootWebhookController;

/**
 * Chatwoot Admin Settings Routes (Krayin CRM Sidebar)
 */
Route::group(['middleware' => ['web', 'user'], 'prefix' => 'admin/chatwoot'], function () {
    Route::get('/settings', [ChatwootAdminController::class, 'index'])->name('admin.chatwoot.settings.index');
    Route::get('/ping', [ChatwootAdminController::class, 'ping'])->name('admin.chatwoot.ping');
    Route::post('/sync-tags', [ChatwootAdminController::class, 'syncTagsNow'])->name('admin.chatwoot.sync_tags');
    Route::post('/clear-logs', [ChatwootAdminController::class, 'clearLogs'])->name('admin.chatwoot.clear_logs');
});

/**
 * Chatwoot Dashboard App Embed Routes
 */
Route::group(['middleware' => ['web']], function () {
    Route::get('/chatwoot/embed', [ChatwootEmbedController::class, 'index'])->name('chatwoot.embed.index');
    Route::post('/chatwoot/embed/search', [ChatwootEmbedController::class, 'search'])->name('chatwoot.embed.search');
    Route::post('/chatwoot/embed/lead/store', [ChatwootEmbedController::class, 'storeLead'])->name('chatwoot.embed.lead.store');
    Route::post('/chatwoot/embed/lead/update-stage', [ChatwootEmbedController::class, 'updateStage'])->name('chatwoot.embed.lead.update-stage');
    Route::post('/chatwoot/embed/activity/store', [ChatwootEmbedController::class, 'storeActivity'])->name('chatwoot.embed.activity.store');
});

/**
 * Chatwoot Inbound Webhook Routes
 */
Route::group(['middleware' => ['api'], 'prefix' => 'api'], function () {
    Route::post('/chatwoot/webhook', [ChatwootWebhookController::class, 'handle'])->name('chatwoot.webhook');
});
