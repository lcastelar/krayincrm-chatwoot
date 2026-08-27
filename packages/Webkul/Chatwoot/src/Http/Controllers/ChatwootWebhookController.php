<?php

namespace Webkul\Chatwoot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Chatwoot\Services\ChatwootApiService;
use Webkul\Chatwoot\Services\SyncContext;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Tag\Models\Tag;

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
        $secret = config('chatwoot.webhook_secret', env('CHATWOOT_WEBHOOK_SECRET', ''));
        if (! empty($secret)) {
            $signature = $request->header('X-Chatwoot-Signature');
            if ($signature !== $secret) {
                $this->logEvent($request->input('event', 'unknown'), 'failed', 401, 'Invalid webhook signature', $request->all());
                return response()->json(['message' => 'Invalid signature'], 401);
            }
        }

        $event = $request->input('event');
        $payload = $request->all();

        Log::info("Chatwoot Webhook received event: {$event}");

        return SyncContext::executeWithoutLoop(function () use ($event, $payload) {
            try {
                $summary = '';
                switch ($event) {
                    case 'contact_created':
                    case 'contact_updated':
                        $summary = $this->syncContact($payload);
                        break;

                    case 'contact_deleted':
                        $summary = $this->deleteContact($payload);
                        break;

                    case 'label_created':
                    case 'tag_created':
                        $summary = $this->syncTagCreated($payload);
                        break;

                    case 'label_updated':
                    case 'tag_updated':
                        $summary = $this->syncTagUpdated($payload);
                        break;

                    case 'label_deleted':
                    case 'tag_deleted':
                        $summary = $this->syncTagDeleted($payload);
                        break;

                    default:
                        $summary = "Evento '{$event}' ignorado (sem ação configurada)";
                        break;
                }

                $this->logEvent($event, 'success', 200, $summary, $payload);
                return response()->json(['status' => 'success', 'summary' => $summary]);
            } catch (\Throwable $e) {
                Log::error("Chatwoot Webhook error on [{$event}]: " . $e->getMessage());
                $this->logEvent($event, 'failed', 500, 'Erro ao processar: ' . $e->getMessage(), $payload, $e->getMessage());
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
            }
        }, 'chatwoot');
    }

    /**
     * Sincronizar criação / edição de contato do Chatwoot no Krayin CRM.
     */
    protected function syncContact(array $payload): string
    {
        $contactData = $payload['contact'] ?? $payload;
        $chatwootContactId = $contactData['id'] ?? null;
        $name = $contactData['name'] ?? null;
        $email = $contactData['email'] ?? null;
        $phone = $contactData['phone_number'] ?? null;
        $tags = $contactData['tags'] ?? $contactData['labels'] ?? [];

        // Chatwoot webhooks do not embed contact labels in the contact_updated payload body.
        // Query the Chatwoot API directly to retrieve the exact real-time assigned tags!
        if ($chatwootContactId) {
            $apiService = app(ChatwootApiService::class);
            $fetchedTags = $apiService->getContactLabels((int) $chatwootContactId);
            if (! empty($fetchedTags)) {
                $tags = $fetchedTags;
            }
        }

        if (! $email && ! $phone && ! $name) {
            return 'Contato sem identificador (e-mail/telefone/nome vazio)';
        }

        // Buscar contato existente por telefone ou email
        $existing = null;
        if ($phone) {
            $cleanPhone = preg_replace('/\D/', '', $phone);
            $existing = DB::table('persons')
                ->where('contact_numbers', 'like', "%{$cleanPhone}%")
                ->orWhere('contact_numbers', 'like', "%{$phone}%")
                ->first();
        }

        if (! $existing && $email) {
            $existing = DB::table('persons')
                ->where('emails', 'like', "%{$email}%")
                ->first();
        }

        $personId = null;

        if ($existing) {
            $personId = $existing->id;

            // Atualizar dados cadastrais
            $updateData = [];
            if ($name && $name !== $existing->name) {
                $updateData['name'] = $name;
            }

            if (! empty($updateData)) {
                DB::table('persons')->where('id', $personId)->update($updateData);
            }
        } else {
            // Criar nova pessoa
            $person = $this->personRepository->create([
                'name'            => $name ?? 'Contato Chatwoot',
                'emails'          => $email ? [['value' => $email, 'label' => 'work']] : [],
                'contact_numbers' => $phone ? [['value' => $phone, 'label' => 'work']] : [],
                'entity_type'     => 'persons',
                'user_id'         => config('chatwoot.default_user_id', 1),
            ]);
            $personId = $person->id;
        }

        // Sincronizar tags/etiquetas vinculadas
        if ($personId) {
            $this->syncPersonTags($personId, is_array($tags) ? $tags : []);
        }

        return "Contato #{$personId} ({$name}) sincronizado com sucesso (" . count($tags) . " tags)";
    }

    /**
     * Sincronizar tags de uma pessoa no Krayin.
     */
    protected function syncPersonTags(int $personId, array $tagNames): void
    {
        // 1. Garantir que as tags existam no catálogo de tags
        $tagIds = [];
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) continue;

            $tag = DB::table('tags')->where('name', $tagName)->first();
            if (! $tag) {
                $tagId = DB::table('tags')->insertGetId([
                    'name'       => $tagName,
                    'color'      => '#51C984',
                    'user_id'    => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $tagIds[] = $tagId;
            } else {
                $tagIds[] = $tag->id;
            }
        }

        // 2. Sincronizar person_tags (espelho exato)
        DB::table('person_tags')->where('person_id', $personId)->delete();
        foreach ($tagIds as $tagId) {
            DB::table('person_tags')->insert([
                'person_id' => $personId,
                'tag_id'    => $tagId,
            ]);
        }

        // 3. Sincronizar nos leads abertos vinculados a essa pessoa
        $leadIds = DB::table('leads')->where('person_id', $personId)->pluck('id')->toArray();
        if (! empty($leadIds)) {
            DB::table('lead_tags')->whereIn('lead_id', $leadIds)->delete();
            foreach ($leadIds as $leadId) {
                foreach ($tagIds as $tagId) {
                    DB::table('lead_tags')->insert([
                        'lead_id' => $leadId,
                        'tag_id'  => $tagId,
                    ]);
                }
            }
        }
    }

    /**
     * Exclusão segura de contato vinda do Chatwoot.
     */
    protected function deleteContact(array $payload): string
    {
        $contactData = $payload['contact'] ?? $payload;
        $email = $contactData['email'] ?? null;
        $phone = $contactData['phone_number'] ?? null;

        $existing = null;
        if ($phone) {
            $cleanPhone = preg_replace('/\D/', '', $phone);
            $existing = DB::table('persons')
                ->where('contact_numbers', 'like', "%{$cleanPhone}%")
                ->orWhere('contact_numbers', 'like', "%{$phone}%")
                ->first();
        }

        if (! $existing && $email) {
            $existing = DB::table('persons')
                ->where('emails', 'like', "%{$email}%")
                ->first();
        }

        if ($existing) {
            $personId = $existing->id;

            // Desvincular Leads com segurança para preservar histórico financeiro
            DB::table('leads')->where('person_id', $personId)->update(['person_id' => null]);

            // Remover person_tags
            DB::table('person_tags')->where('person_id', $personId)->delete();

            // Excluir pessoa
            DB::table('persons')->where('id', $personId)->delete();

            return "Contato #{$personId} excluído com sucesso (Negócios desvinculados preservados)";
        }

        return 'Contato não encontrado para exclusão';
    }

    /**
     * Sincronizar nova etiqueta criada no Chatwoot.
     */
    protected function syncTagCreated(array $payload): string
    {
        $label = $payload['label'] ?? $payload;
        $title = $label['title'] ?? null;
        $color = $label['color'] ?? '#51C984';

        if (! $title) return 'Etiqueta sem nome';

        $exists = DB::table('tags')->where('name', $title)->first();
        if (! $exists) {
            DB::table('tags')->insert([
                'name'       => $title,
                'color'      => $color,
                'user_id'    => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return "Etiqueta '{$title}' criada no catálogo do Krayin";
        }

        return "Etiqueta '{$title}' já existe";
    }

    /**
     * Sincronizar etiqueta editada no Chatwoot.
     */
    protected function syncTagUpdated(array $payload): string
    {
        $label = $payload['label'] ?? $payload;
        $title = $label['title'] ?? null;
        $color = $label['color'] ?? '#51C984';

        if (! $title) return 'Etiqueta sem nome';

        DB::table('tags')->where('name', $title)->update([
            'color'      => $color,
            'updated_at' => now(),
        ]);

        return "Etiqueta '{$title}' atualizada com a cor {$color}";
    }

    /**
     * Sincronizar etiqueta deletada no Chatwoot.
     */
    protected function syncTagDeleted(array $payload): string
    {
        $label = $payload['label'] ?? $payload;
        $title = $label['title'] ?? null;

        if (! $title) return 'Etiqueta sem nome';

        $tag = DB::table('tags')->where('name', $title)->first();
        if ($tag) {
            DB::table('person_tags')->where('tag_id', $tag->id)->delete();
            DB::table('lead_tags')->where('tag_id', $tag->id)->delete();
            DB::table('tags')->where('id', $tag->id)->delete();
            return "Etiqueta '{$title}' removida do Krayin e desvinculada de contatos/leads";
        }

        return "Etiqueta '{$title}' não encontrada para exclusão";
    }

    /**
     * Registrar evento no log de auditoria.
     */
    protected function logEvent(string $event, string $status, int $code, string $summary, array $payload, ?string $error = null): void
    {
        try {
            DB::table('chatwoot_webhook_logs')->insert([
                'event'         => $event,
                'source'        => 'chatwoot',
                'status'        => $status,
                'response_code' => $code,
                'summary'       => $summary,
                'payload'       => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'error_message' => $error,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("Could not log webhook event: " . $e->getMessage());
        }
    }
}
