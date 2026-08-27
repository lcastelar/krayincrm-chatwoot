<?php

namespace Webkul\Chatwoot\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatwootApiService
{
    protected string $baseUrl;
    protected string $apiToken;
    protected int $accountId;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('chatwoot.url', env('CHATWOOT_URL', 'https://chat.dmarelectronics.com')), '/');
        $this->apiToken = config('chatwoot.api_token', env('CHATWOOT_API_TOKEN', ''));
        $this->accountId = (int) config('chatwoot.account_id', env('CHATWOOT_ACCOUNT_ID', 1));
    }

    /**
     * Get base HTTP client with auth headers.
     */
    protected function client()
    {
        return Http::withHeaders([
            'api_access_token' => $this->apiToken,
            'Accept'           => 'application/json',
            'Content-Type'     => 'application/json',
        ])->timeout(10);
    }

    /**
     * Test connection to Chatwoot API.
     */
    public function ping(): array
    {
        if (empty($this->baseUrl) || empty($this->apiToken)) {
            return [
                'success' => false,
                'message' => 'Chatwoot URL ou API Token não configurados no ambiente.',
            ];
        }

        try {
            $response = $this->client()->get("{$this->baseUrl}/api/v1/accounts/{$this->accountId}");
            
            if ($response->successful()) {
                $account = $response->json();
                return [
                    'success'      => true,
                    'account_name' => $account['name'] ?? "Account #{$this->accountId}",
                    'account_id'   => $this->accountId,
                    'status_code'  => $response->status(),
                ];
            }

            return [
                'success'     => false,
                'message'     => 'Falha ao autenticar com o Chatwoot: ' . ($response->json('message') ?? $response->body()),
                'status_code' => $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Erro de conexão com o Chatwoot: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get all active labels from Chatwoot.
     */
    public function getLabels(): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/labels");
            if ($response->successful()) {
                $data = $response->json();
                return $data['payload'] ?? $data ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("ChatwootApiService getLabels failed: " . $e->getMessage());
        }
        return [];
    }

    /**
     * Create a label in Chatwoot.
     */
    public function createLabel(string $title, string $color = '#51C984', ?string $description = null): ?array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/labels", [
                'title'           => $title,
                'color'           => $color,
                'description'     => $description ?? '',
                'show_on_sidebar' => true,
            ]);

            if ($response->successful()) {
                return $response->json('payload') ?? $response->json();
            }
        } catch (\Throwable $e) {
            Log::error("ChatwootApiService createLabel failed: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Update a label in Chatwoot.
     */
    public function updateLabel(int $labelId, string $title, string $color, ?string $description = null): ?array
    {
        try {
            $response = $this->client()->patch("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/labels/{$labelId}", [
                'title'       => $title,
                'color'       => $color,
                'description' => $description ?? '',
            ]);

            if ($response->successful()) {
                return $response->json('payload') ?? $response->json();
            }
        } catch (\Throwable $e) {
            Log::error("ChatwootApiService updateLabel failed: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Delete a label in Chatwoot.
     */
    public function deleteLabel(int $labelId): bool
    {
        try {
            $response = $this->client()->delete("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/labels/{$labelId}");
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("ChatwootApiService deleteLabel failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Search contact in Chatwoot by phone, email or query.
     */
    public function searchContact(string $query): ?array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts/search", [
                'q' => $query,
            ]);

            if ($response->successful()) {
                $payload = $response->json('payload') ?? [];
                return ! empty($payload) ? $payload[0] : null;
            }
        } catch (\Throwable $e) {
            Log::error("ChatwootApiService searchContact failed: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Create contact in Chatwoot.
     */
    public function createContact(array $data): ?array
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts", $data);
            if ($response->successful()) {
                return $response->json('payload') ?? $response->json();
            }
        } catch (\Throwable $e) {
            Log::error("ChatwootApiService createContact failed: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Update contact in Chatwoot.
     */
    public function updateContact(int $contactId, array $data): ?array
    {
        try {
            $response = $this->client()->put("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts/{$contactId}", $data);
            if ($response->successful()) {
                return $response->json('payload') ?? $response->json();
            }
        } catch (\Throwable $e) {
            Log::error("ChatwootApiService updateContact failed: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Delete contact in Chatwoot.
     */
    public function deleteContact(int $contactId): bool
    {
        try {
            $response = $this->client()->delete("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts/{$contactId}");
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("ChatwootApiService deleteContact failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get labels assigned to a specific contact in Chatwoot.
     */
    public function getContactLabels(int $contactId): array
    {
        try {
            $response = $this->client()->get("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts/{$contactId}/labels");
            if ($response->successful()) {
                $data = $response->json();
                return $data['payload'] ?? $data ?? [];
            }
        } catch (\Throwable $e) {
            Log::error("ChatwootApiService getContactLabels failed: " . $e->getMessage());
        }
        return [];
    }

    /**
     * Update contact labels (tags) in Chatwoot.
     */
    public function updateContactLabels(int $contactId, array $labels): bool
    {
        try {
            $response = $this->client()->post("{$this->baseUrl}/api/v1/accounts/{$this->accountId}/contacts/{$contactId}/labels", [
                'labels' => array_values(array_unique($labels)),
            ]);
            return $response->successful();
        } catch (\Throwable $e) {
            Log::error("ChatwootApiService updateContactLabels failed: " . $e->getMessage());
            return false;
        }
    }
}
