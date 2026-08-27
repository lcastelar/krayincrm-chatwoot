<?php

namespace Webkul\Chatwoot\Observers;

use Illuminate\Support\Facades\Log;
use Webkul\Chatwoot\Services\ChatwootApiService;
use Webkul\Chatwoot\Services\SyncContext;
use Webkul\Contact\Models\Person;

class PersonObserver
{
    public function __construct(
        protected ChatwootApiService $chatwootApi
    ) {}

    /**
     * Handle the Person "created" event in Krayin.
     */
    public function created(Person $person): void
    {
        if (SyncContext::isFromChatwoot()) {
            return;
        }

        try {
            $phone = is_array($person->contact_numbers) ? ($person->contact_numbers[0]['value'] ?? null) : null;
            $email = is_array($person->emails) ? ($person->emails[0]['value'] ?? null) : null;

            if (! $phone && ! $email) {
                return;
            }

            SyncContext::executeWithoutLoop(function () use ($person, $phone, $email) {
                $payload = [
                    'name'         => $person->name,
                    'phone_number' => $phone,
                    'email'        => $email,
                    'custom_attributes' => [
                        'krayin_person_id' => $person->id,
                    ],
                ];

                $this->chatwootApi->createContact($payload);
            }, 'krayin');
        } catch (\Throwable $e) {
            Log::error("PersonObserver created sync failed: " . $e->getMessage());
        }
    }

    /**
     * Handle the Person "updated" event in Krayin.
     */
    public function updated(Person $person): void
    {
        if (SyncContext::isFromChatwoot()) {
            return;
        }

        try {
            $phone = is_array($person->contact_numbers) ? ($person->contact_numbers[0]['value'] ?? null) : null;
            $email = is_array($person->emails) ? ($person->emails[0]['value'] ?? null) : null;

            if (! $phone && ! $email) {
                return;
            }

            SyncContext::executeWithoutLoop(function () use ($person, $phone, $email) {
                $searchQuery = $phone ?? $email;
                $chatwootContact = $this->chatwootApi->searchContact($searchQuery);

                $payload = [
                    'name'         => $person->name,
                    'phone_number' => $phone,
                    'email'        => $email,
                    'custom_attributes' => [
                        'krayin_person_id' => $person->id,
                    ],
                ];

                if ($chatwootContact && isset($chatwootContact['id'])) {
                    $this->chatwootApi->updateContact($chatwootContact['id'], $payload);

                    // Sync tags/labels
                    $tags = $person->tags->pluck('name')->toArray();
                    $this->chatwootApi->updateContactLabels($chatwootContact['id'], $tags);
                } else {
                    $created = $this->chatwootApi->createContact($payload);
                    if ($created && isset($created['id'])) {
                        $tags = $person->tags->pluck('name')->toArray();
                        $this->chatwootApi->updateContactLabels($created['id'], $tags);
                    }
                }
            }, 'krayin');
        } catch (\Throwable $e) {
            Log::error("PersonObserver updated sync failed: " . $e->getMessage());
        }
    }

    /**
     * Handle the Person "deleted" event in Krayin.
     */
    public function deleted(Person $person): void
    {
        if (SyncContext::isFromChatwoot()) {
            return;
        }

        try {
            $phone = is_array($person->contact_numbers) ? ($person->contact_numbers[0]['value'] ?? null) : null;
            $email = is_array($person->emails) ? ($person->emails[0]['value'] ?? null) : null;

            if (! $phone && ! $email) {
                return;
            }

            SyncContext::executeWithoutLoop(function () use ($phone, $email) {
                $searchQuery = $phone ?? $email;
                $chatwootContact = $this->chatwootApi->searchContact($searchQuery);

                if ($chatwootContact && isset($chatwootContact['id'])) {
                    $this->chatwootApi->deleteContact($chatwootContact['id']);
                }
            }, 'krayin');
        } catch (\Throwable $e) {
            Log::error("PersonObserver deleted sync failed: " . $e->getMessage());
        }
    }
}
