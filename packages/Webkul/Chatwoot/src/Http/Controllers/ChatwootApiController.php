<?php

namespace Webkul\Chatwoot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Contact\Models\Person;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Product\Models\Product;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Chatwoot\Services\LeadValueSynchronizer;

class ChatwootApiController extends Controller
{
    public function __construct(
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected ActivityRepository $activityRepository,
        protected ProductRepository $productRepository,
        protected LeadValueSynchronizer $leadValueSynchronizer
    ) {}

    /**
     * Valida o token de autenticação recebido no cabeçalho ou query param.
     */
    protected function authenticate(Request $request): void
    {
        $token = $request->bearerToken() ?: $request->header('X-API-Key') ?: $request->query('api_token');
        $expected = config('chatwoot.krayin_api_token') ?: env('KRAYIN_API_TOKEN');

        if (! is_string($expected)
            || $expected === ''
            || ! is_string($token)
            || ! hash_equals($expected, $token)
        ) {
            abort(response()->json(['error' => 'Unauthorized: Invalid or missing API token'], 401));
        }
    }

    /**
     * Extrai dados mesclando input, json e array de payload.
     */
    protected function getPayload(Request $request): array
    {
        $content = $request->getContent();
        $json = json_decode($content, true);

        if (is_array($json)) {
            return array_merge($request->all(), $json);
        }

        return $request->all();
    }

    /**
     * GET /api/v1/contacts/persons ou GET /api/admin/contacts/persons
     * Busca pessoas/contatos no CRM por telefone, e-mail ou nome.
     */
    public function searchPersons(Request $request)
    {
        $this->authenticate($request);

        $payload = $this->getPayload($request);
        $search = $payload['search'] ?? $payload['phone'] ?? $payload['email'] ?? null;

        $query = Person::query()->with('attribute_values');

        if ($search) {
            $cleanSearch = preg_replace('/\D/', '', (string) $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('emails', 'like', "%{$search}%")
                  ->orWhere('contact_numbers', 'like', "%{$search}%");

                if (! empty($cleanSearch)) {
                    $q->orWhere('contact_numbers', 'like', "%{$cleanSearch}%");
                }
            });
        }

        $persons = $query->limit(50)->get();

        return response()->json(['data' => $persons->map(fn (Person $person) => [
            'id'              => $person->id,
            'name'            => $person->getAttribute('name'),
            'emails'          => $person->getAttribute('emails'),
            'contact_numbers' => $person->getAttribute('contact_numbers'),
            'job_title'       => $person->getAttribute('job_title'),
            'user_id'         => $person->user_id,
            'organization_id' => $person->organization_id,
            'created_at'      => $person->created_at,
            'updated_at'      => $person->updated_at,
        ])->values()]);
    }

    /**
     * GET /api/v1/leads ou GET /api/admin/leads
     * Lista leads do contato (ou busca por título/contrato).
     */
    public function getLeads(Request $request)
    {
        $this->authenticate($request);

        $payload = $this->getPayload($request);
        $personId = $payload['person_id'] ?? null;
        $search = $payload['search'] ?? null;

        $query = Lead::query();

        if ($personId) {
            $query->where('person_id', $personId);
        }

        if ($search) {
            $query->where(function ($sub) use ($search) {
                $sub->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $leads = $query->orderBy('id', 'desc')->limit(50)->get();

        return response()->json(['data' => $leads]);
    }

    /**
     * POST /api/v1/leads ou POST /api/admin/leads
     * Cria um novo Lead / Negócio no Krayin CRM.
     */
    public function createLead(Request $request)
    {
        $this->authenticate($request);

        $payload = $this->getPayload($request);

        $title = $payload['title'] ?? null;
        $personId = $payload['person_id'] ?? null;

        if (empty($title) || empty($personId)) {
            return response()->json([
                'success' => false,
                'message' => 'Campos obrigatórios ausentes: title e person_id são necessários.',
            ], 422);
        }

        try {
            $defaultUserId = config('chatwoot.default_user_id', 1);
            $userId = $payload['user_id'] ?? $defaultUserId;

            $pipelineId = $payload['lead_pipeline_id'] ?? config('chatwoot.default_pipeline_id', 2);
            $stageId = $payload['lead_pipeline_stage_id'] ?? config('chatwoot.default_stage_id', 11);
            $status = $payload['status'] ?? 'won';

            $leadData = [
                'title'                  => $title,
                'description'            => $payload['description'] ?? '',
                'lead_value'             => $payload['lead_value'] ?? 0,
                'status'                 => $status,
                'person_id'              => (int) $personId,
                'lead_pipeline_id'       => (int) $pipelineId,
                'lead_pipeline_stage_id' => (int) $stageId,
                'lead_source_id'         => $payload['lead_source_id'] ?? 5, // Direto
                'lead_type_id'           => $payload['lead_type_id'] ?? 1,   // Novo Negócio
                'user_id'                => (int) $userId,
                'entity_type'            => 'leads',
            ];

            $lead = $this->leadRepository->create($leadData);

            return response()->json([
                'success' => true,
                'message' => 'Lead criado com sucesso no Krayin CRM!',
                'data'    => $lead,
                'id'      => $lead->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('ChatwootApiController@createLead error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar Lead: ' . $e->getMessage(),
                'trace'   => $e->getFile() . ':' . $e->getLine(),
            ], 500);
        }
    }

    /**
     * PUT /api/v1/leads/{id} ou PUT /api/admin/leads/{id}
     * Atualiza um Lead existente.
     */
    public function updateLead(Request $request, $id)
    {
        $this->authenticate($request);

        $payload = $this->getPayload($request);

        try {
            $updateData = ['entity_type' => 'leads'];

            if (isset($payload['title'])) {
                $updateData['title'] = $payload['title'];
            }
            if (isset($payload['description'])) {
                $updateData['description'] = $payload['description'];
            }
            if (isset($payload['lead_value'])) {
                $updateData['lead_value'] = $payload['lead_value'];
            }
            if (isset($payload['status'])) {
                $updateData['status'] = $payload['status'];
            }
            if (isset($payload['lead_pipeline_id'])) {
                $updateData['lead_pipeline_id'] = (int) $payload['lead_pipeline_id'];
            }
            if (isset($payload['lead_pipeline_stage_id'])) {
                $updateData['lead_pipeline_stage_id'] = (int) $payload['lead_pipeline_stage_id'];
            }

            $lead = $this->leadRepository->update($updateData, (int) $id);

            return response()->json([
                'success' => true,
                'message' => 'Lead atualizado com sucesso!',
                'data'    => $lead,
                'id'      => $lead->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('ChatwootApiController@updateLead error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar Lead: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/products ou GET /api/admin/products
     * Busca produtos no catálogo do Krayin CRM.
     */
    public function searchProducts(Request $request)
    {
        $this->authenticate($request);

        $payload = $this->getPayload($request);
        $search = $payload['search'] ?? $payload['query'] ?? null;

        $query = Product::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->with('attribute_values')->limit(50)->get();

        return response()->json(['data' => $products->map(fn (Product $product) => [
            'id'          => $product->id,
            'name'        => $product->getAttribute('name'),
            'sku'         => $product->getAttribute('sku'),
            'description' => $product->getAttribute('description'),
            'quantity'    => $product->getAttribute('quantity'),
            'price'       => $product->getAttribute('price'),
            'created_at'  => $product->created_at,
            'updated_at'  => $product->updated_at,
        ])->values()]);
    }

    /**
     * POST /api/v1/products ou POST /api/admin/products
     * Cria um produto no catálogo do Krayin CRM.
     */
    public function createProduct(Request $request)
    {
        $this->authenticate($request);

        $payload = $this->getPayload($request);
        $validated = Validator::make($payload, $this->productRules())->validate();
        $validated['entity_type'] = $validated['entity_type'] ?? 'products';
        $validated['quantity'] = $validated['quantity'] ?? 0;
        $validated['price'] = $validated['price'] ?? 0;

        try {
            $product = DB::transaction(fn () => $this->productRepository->create($validated));

            return response()->json([
                'success' => true,
                'message' => 'Produto criado com sucesso!',
                'data'    => $this->serializeProduct((int) $product->id),
                'id'      => $product->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('ChatwootApiController@createProduct failed.', [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível criar o produto.',
            ], 500);
        }
    }

    /**
     * PUT /api/v1/products/{id} ou PUT /api/admin/products/{id}
     * Atualiza um produto existente no catálogo do Krayin CRM.
     */
    public function updateProduct(Request $request, $id)
    {
        $this->authenticate($request);

        $product = Product::query()->find($id);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado.',
            ], 404);
        }

        $payload = $this->getPayload($request);
        $validated = Validator::make($payload, $this->productRules((int) $product->id))->validate();
        $validated['entity_type'] = $validated['entity_type'] ?? 'products';

        try {
            $product = DB::transaction(fn () => $this->productRepository->update($validated, (int) $product->id));

            return response()->json([
                'success' => true,
                'message' => 'Produto atualizado com sucesso!',
                'data'    => $this->serializeProduct((int) $product->id),
                'id'      => $product->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('ChatwootApiController@updateProduct failed.', [
                'product_id' => (int) $product->id,
                'exception'  => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível atualizar o produto.',
            ], 500);
        }
    }

    /**
     * Validation rules shared by product creation and update.
     */
    private function productRules(?int $productId = null): array
    {
        return [
            'name'        => ['required', 'string'],
            'sku'         => ['required', 'string', Rule::unique('products', 'sku')->ignore($productId)],
            'description' => ['nullable', 'string'],
            'quantity'    => ['nullable', 'integer', 'min:0'],
            'price'       => ['nullable', 'numeric', 'min:0'],
            'entity_type' => ['sometimes', Rule::in(['products'])],
        ];
    }

    /**
     * Build a stable API payload without exposing custom attribute internals.
     */
    private function serializeProduct(int $productId): array
    {
        $product = Product::query()->with('attribute_values')->findOrFail($productId);

        return [
            'id'          => $product->id,
            'name'        => $product->getAttribute('name'),
            'sku'         => $product->getAttribute('sku'),
            'description' => $product->getAttribute('description'),
            'quantity'    => $product->getAttribute('quantity'),
            'price'       => $product->getAttribute('price'),
            'created_at'  => $product->created_at,
            'updated_at'  => $product->updated_at,
        ];
    }

    /**
     * PUT|POST /api/v1/leads/{id}/products ou PUT /api/admin/leads/product/{id}
     * Vincula um produto ao Lead.
     */
    public function addLeadProduct(Request $request, $id)
    {
        $this->authenticate($request);

        $payload = $this->getPayload($request);
        $productId = $payload['product_id'] ?? null;

        if (empty($productId)) {
            return response()->json([
                'success' => false,
                'message' => 'Campo obrigatório ausente: product_id é necessário.',
            ], 422);
        }

        try {
            $quantity = $payload['quantity'] ?? 1;
            $price = $payload['price'] ?? 0;
            $amount = $quantity * $price;

            $leadValue = DB::transaction(function () use ($id, $productId, $quantity, $price, $amount) {
                // Insere ou atualiza na tabela lead_products.
                DB::table('lead_products')->updateOrInsert(
                    [
                        'lead_id'    => (int) $id,
                        'product_id' => (int) $productId,
                    ],
                    [
                        'quantity'   => $quantity,
                        'price'      => $price,
                        'amount'     => $amount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                return $this->recalculateLeadValue((int) $id);
            });

            return response()->json([
                'success'    => true,
                'message'    => 'Produto vinculado com sucesso ao Lead!',
                'lead_id'    => (int) $id,
                'product_id' => (int) $productId,
                'lead_value' => $leadValue,
            ]);
        } catch (\Throwable $e) {
            Log::error('ChatwootApiController@addLeadProduct error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao vincular produto: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Recalculate and return the opportunity value from linked products.
     */
    private function recalculateLeadValue(int $leadId): float
    {
        return $this->leadValueSynchronizer->recalculate($leadId);
    }

    /**
     * POST /api/v1/activities ou POST /api/admin/activities
     * Adiciona uma Nota Técnica ou Atividade vinculada ao Lead.
     */
    public function createActivity(Request $request)
    {
        $this->authenticate($request);

        $payload = $this->getPayload($request);
        $leadId = $payload['lead_id'] ?? null;
        $comment = $payload['comment'] ?? null;

        if (empty($leadId) || empty($comment)) {
            return response()->json([
                'success' => false,
                'message' => 'Campos obrigatórios ausentes: lead_id e comment são necessários.',
            ], 422);
        }

        try {
            $defaultUserId = config('chatwoot.default_user_id', 1);
            $userId = $payload['user_id'] ?? $defaultUserId;
            $type = $payload['type'] ?? 'note';
            $title = $payload['title'] ?? 'Nota Técnica';

            $activity = $this->activityRepository->create([
                'type'    => $type,
                'title'   => $title,
                'comment' => $comment,
                'is_done' => $payload['is_done'] ?? 1,
                'user_id' => (int) $userId,
            ]);

            // Vincula a atividade ao Lead na tabela pivot lead_activities
            DB::table('lead_activities')->updateOrInsert([
                'lead_id'     => (int) $leadId,
                'activity_id' => (int) $activity->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Atividade / Nota Técnica criada com sucesso!',
                'data'    => $activity,
                'id'      => $activity->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('ChatwootApiController@createActivity error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar atividade: ' . $e->getMessage(),
            ], 500);
        }
    }
}
