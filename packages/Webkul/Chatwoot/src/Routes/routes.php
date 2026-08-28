<?php

use Illuminate\Support\Facades\Route;
use Webkul\Chatwoot\Http\Controllers\ChatwootAdminController;
use Webkul\Chatwoot\Http\Controllers\ChatwootApiController;
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
 * Chatwoot Dashboard App Embed Routes (Requires logged in Krayin user)
 */
Route::group(['middleware' => ['web', 'user']], function () {
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

/**
 * Krayin CRM Integration REST API Routes (Used by n8n and External Automations)
 * Support both /api/v1/... and /api/admin/... prefixes.
 */
foreach (['api/v1', 'api/admin'] as $prefix) {
    Route::group(['middleware' => ['api'], 'prefix' => $prefix], function () {
        // Persons / Contacts
        Route::get('/contacts/persons', [ChatwootApiController::class, 'searchPersons'])->name('api.contacts.persons.search');
        Route::get('/persons/search', [ChatwootApiController::class, 'searchPersons'])->name('api.persons.search');

        // Leads / Deals
        Route::get('/leads', [ChatwootApiController::class, 'getLeads'])->name('api.leads.index');
        Route::post('/leads', [ChatwootApiController::class, 'createLead'])->name('api.leads.store');
        Route::put('/leads/{id}', [ChatwootApiController::class, 'updateLead'])->name('api.leads.update');
        Route::post('/leads/{id}', [ChatwootApiController::class, 'updateLead'])->name('api.leads.update_post');

        // Products
        Route::get('/products', [ChatwootApiController::class, 'searchProducts'])->name('api.products.search');
        Route::put('/leads/product/{id}', [ChatwootApiController::class, 'addLeadProduct'])->name('api.leads.product.attach');
        Route::post('/leads/{id}/products', [ChatwootApiController::class, 'addLeadProduct'])->name('api.leads.products.attach_post');
        Route::put('/leads/{id}/products', [ChatwootApiController::class, 'addLeadProduct'])->name('api.leads.products.attach_put');

        // Activities / Notes
        Route::post('/activities', [ChatwootApiController::class, 'createActivity'])->name('api.activities.store');
        Route::post('/leads/{id}/notes', [ChatwootApiController::class, 'createActivity'])->name('api.leads.notes.store');
    });
}
