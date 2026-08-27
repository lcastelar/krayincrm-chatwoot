<?php

namespace Webkul\Chatwoot\Observers;

use Illuminate\Support\Facades\Log;
use Webkul\Chatwoot\Services\ChatwootApiService;
use Webkul\Chatwoot\Services\SyncContext;
use Webkul\Tag\Models\Tag;

class TagObserver
{
    public function __construct(
        protected ChatwootApiService $chatwootApi
    ) {}

    /**
     * Handle the Tag "created" event in Krayin.
     */
    public function created(Tag $tag): void
    {
        if (SyncContext::isFromChatwoot()) {
            return;
        }

        try {
            SyncContext::executeWithoutLoop(function () use ($tag) {
                $color = $tag->color ?? '#51C984';
                $this->chatwootApi->createLabel($tag->name, $color, "Criado via Krayin CRM");
            }, 'krayin');
        } catch (\Throwable $e) {
            Log::error("TagObserver created sync failed: " . $e->getMessage());
        }
    }

    /**
     * Handle the Tag "updated" event in Krayin.
     */
    public function updated(Tag $tag): void
    {
        if (SyncContext::isFromChatwoot()) {
            return;
        }

        try {
            SyncContext::executeWithoutLoop(function () use ($tag) {
                $labels = $this->chatwootApi->getLabels();
                $originalName = $tag->getOriginal('name') ?? $tag->name;
                
                $match = collect($labels)->firstWhere('title', $originalName);

                if ($match && isset($match['id'])) {
                    $this->chatwootApi->updateLabel(
                        (int) $match['id'],
                        $tag->name,
                        $tag->color ?? '#51C984',
                        $match['description'] ?? ''
                    );
                } else {
                    $this->chatwootApi->createLabel($tag->name, $tag->color ?? '#51C984');
                }
            }, 'krayin');
        } catch (\Throwable $e) {
            Log::error("TagObserver updated sync failed: " . $e->getMessage());
        }
    }

    /**
     * Handle the Tag "deleted" event in Krayin.
     */
    public function deleted(Tag $tag): void
    {
        if (SyncContext::isFromChatwoot()) {
            return;
        }

        try {
            SyncContext::executeWithoutLoop(function () use ($tag) {
                $labels = $this->chatwootApi->getLabels();
                $match = collect($labels)->firstWhere('title', $tag->name);

                if ($match && isset($match['id'])) {
                    $this->chatwootApi->deleteLabel((int) $match['id']);
                }
            }, 'krayin');
        } catch (\Throwable $e) {
            Log::error("TagObserver deleted sync failed: " . $e->getMessage());
        }
    }
}
