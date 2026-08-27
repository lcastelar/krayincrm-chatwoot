<?php

namespace Webkul\Chatwoot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Webkul\Contact\Repositories\PersonRepository;

class ChatwootWebhookController extends Controller
{
    public function __construct(
        protected PersonRepository $personRepository
    ) {}

    /**
     * Handle incoming Chatwoot Webhook payload.
     */
    public function handle(Request $request)
    {
        $secret = config('chatwoot.webhook_secret');
        if (! empty($secret)) {
            $signature = $request->header('X-Chatwoot-Signature');
            if ($signature !== $secret) {
                return response()->json(['message' => 'Invalid signature'], 401);
            }
        }

        $event = $request->input('event');
        Log::info("Chatwoot Webhook received event: {$event}");

        switch ($event) {
            case 'contact_created':
            case 'contact_updated':
                $this->syncContact($request->all());
                break;
            default:
                break;
        }

        return response()->json(['status' => 'success']);
    }

    protected function syncContact(array $payload)
    {
        $email = $payload['email'] ?? null;
        $name = $payload['name'] ?? null;
        $phone = $payload['phone_number'] ?? null;

        if (! $email && ! $phone) {
            return;
        }

        $existing = null;
        if ($email) {
            $existing = $this->personRepository->scopeQuery(fn($q) => $q->where('emails', 'like', "%{$email}%"))->first();
        }

        if (! $existing && $name) {
            $this->personRepository->create([
                'name' => $name,
                'emails' => $email ? [['value' => $email, 'label' => 'work']] : [],
                'contact_numbers' => $phone ? [['value' => $phone, 'label' => 'work']] : [],
                'entity_type' => 'persons',
                'user_id' => config('chatwoot.default_user_id', 1),
            ]);
        }
    }
}
