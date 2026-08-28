<?php

namespace Webkul\Chatwoot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Activity\Repositories\ActivityRepository;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\StageRepository;
use Webkul\Product\Repositories\ProductRepository;

class ChatwootApiController extends Controller
{
    public function __construct(
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected PipelineRepository $pipelineRepository,
        protected StageRepository $stageRepository,
        protected ActivityRepository $activityRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Valida o token de autenticação recebido no cabeçalho ou query param.
     */
    protected function authenticate(Request $request): void
    {
        $token = $request->bearerToken() ?: $request->header('X-API-Key') ?: $request->query('api_token');
        $expected = config('chatwoot.api_token') ?: env('KRAYIN_API_TOKEN') ?: env('CHATWOOT_API_TOKEN') ?: env('CHATWOOT_EMBED_SECRET');

        if (! empty($expected) && $token !== $expected) {
            abort(response()->json(['error' => 'Unauthorized: Invalid or missing API token'], 401));
        }
    }

    /**
     * GET /api/v1/contacts/persons ou GET /api/admin/contacts/persons
     * Busca pessoas/contatos no CRM por telefone, e-mail ou nome.
     */
    public function searchPersons(Request $request)
    {
        $this->authenticate($request);

        $search = $request->input('search') ?: $request->input('phone') ?: $request->input('email');

        if (! $search) {
            $persons = $this->personRepository->all();
            return response()->json(['data' => $persons]);
        }

        $cleanSearch = preg_replace('/\D/', '', $search);

        $persons = $this->personRepository->scopeQuery(function ($query) use ($search, $cleanSearch) {
            return $query->where('name', 'like', "%{$search}%")
                ->orWhere('emails', 'like', "%{$search}%")
                ->orWhere('contact_numbers', 'like', "%{$search}%")
                ->when(! empty($cleanSearch), function ($q) use ($cleanSearch) {
                    return $q->orWhere('contact_numbers', 'like', "%{$cleanSearch}%");
                });
        })->get();

        return response()->json(['data' => $persons]);
    }

    /**
     * GET /api/v1/leads ou GET /api/admin/leads
     * Lista leads do contato (ou busca por título/contrato).
     */
    public function getLeads(Request $request)
    {
        $this->authenticate($request);

        $personId = $request->input('person_id');
        $search = $request->input('search');

        $query = $this->leadRepository->scopeQuery(function ($q) use ($personId, $search) {
            if ($personId) {
                $q->where('person_id', $personId);
            }
            if ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
            return $q->orderBy('id', 'desc');
        });

        $leads = $query->get();

        return response()->json(['data' => $leads]);
    }

    /**
     * POST /api/v1/leads ou POST /api/admin/leads
     * Cria um novo Lead / Negócio no Krayin CRM.
     */
    public function createLead(Request $request)
    {
        $this->authenticate($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'person_id' => 'required|integer',
            'lead_pipeline_id' => 'nullable|integer',
            'lead_pipeline_stage_id' => 'nullable|integer',
        ]);

        try {
            $defaultUserId = config('chatwoot.default_user_id', 1);
            $userId = $request->input('user_id', $defaultUserId);

            $pipelineId = $request->input('lead_pipeline_id', config('chatwoot.default_pipeline_id', 2));
            $stageId = $request->input('lead_pipeline_stage_id', config('chatwoot.default_stage_id', 5));
            $status = $request->input('status', 'won');

            $leadData = [
                'title' => $request->input('title'),
                'description' => $request->input('description', ''),
                'lead_value' => $request->input('lead_value', 0),
                'status' => $status,
                'person_id' => $request->input('person_id'),
                'lead_pipeline_id' => $pipelineId,
                'lead_pipeline_stage_id' => $stageId,
                'lead_source_id' => $request->input('lead_source_id', 5), // Direto
                'lead_type_id' => $request->input('lead_type_id', 1),     // Novo Negócio
                'user_id' => $userId,
                'entity_type' => 'leads',
            ];

            $lead = $this->leadRepository->create($leadData);

            return response()->json([
                'success' => true,
                'message' => 'Lead criado com sucesso no Krayin CRM!',
                'data' => $lead,
                'id' => $lead->id,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('ChatwootApiController@createLead error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar Lead: ' . $e->getMessage(),
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

        try {
            $updateData = ['entity_type' => 'leads'];

            if ($request->has('title')) {
                $updateData['title'] = $request->input('title');
            }
            if ($request->has('description')) {
                $updateData['description'] = $request->input('description');
            }
            if ($request->has('lead_value')) {
                $updateData['lead_value'] = $request->input('lead_value');
            }
            if ($request->has('status')) {
                $updateData['status'] = $request->input('status');
            }
            if ($request->has('lead_pipeline_id')) {
                $updateData['lead_pipeline_id'] = $request->input('lead_pipeline_id');
            }
            if ($request->has('lead_pipeline_stage_id')) {
                $updateData['lead_pipeline_stage_id'] = $request->input('lead_pipeline_stage_id');
            }

            $lead = $this->leadRepository->update($updateData, $id);

            return response()->json([
                'success' => true,
                'message' => 'Lead atualizado com sucesso!',
                'data' => $lead,
                'id' => $lead->id,
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

        $search = $request->input('search') ?: $request->input('query');

        if (! $search) {
            $products = $this->productRepository->all();
            return response()->json(['data' => $products]);
        }

        $products = $this->productRepository->scopeQuery(function ($q) use ($search) {
            return $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        })->get();

        return response()->json(['data' => $products]);
    }

    /**
     * PUT|POST /api/v1/leads/{id}/products ou PUT /api/admin/leads/product/{id}
     * Vincula um produto ao Lead.
     */
    public function addLeadProduct(Request $request, $id)
    {
        $this->authenticate($request);

        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'nullable|numeric',
            'price' => 'nullable|numeric',
        ]);

        try {
            $productId = $request->input('product_id');
            $quantity = $request->input('quantity', 1);
            $price = $request->input('price', 0);
            $amount = $quantity * $price;

            // Insere ou atualiza na tabela lead_products
            DB::table('lead_products')->updateOrInsert(
                [
                    'lead_id' => $id,
                    'product_id' => $productId,
                ],
                [
                    'quantity' => $quantity,
                    'price' => $price,
                    'amount' => $amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Produto vinculado com sucesso ao Lead!',
                'lead_id' => $id,
                'product_id' => $productId,
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
     * POST /api/v1/activities ou POST /api/admin/activities
     * Adiciona uma Nota Técnica ou Atividade vinculada ao Lead.
     */
    public function createActivity(Request $request)
    {
        $this->authenticate($request);

        $request->validate([
            'lead_id' => 'required|integer',
            'comment' => 'required|string',
        ]);

        try {
            $defaultUserId = config('chatwoot.default_user_id', 1);
            $userId = $request->input('user_id', $defaultUserId);
            $type = $request->input('type', 'note');
            $title = $request->input('title', 'Nota Técnica');
            $leadId = $request->input('lead_id');

            $activity = $this->activityRepository->create([
                'type' => $type,
                'title' => $title,
                'comment' => $request->input('comment'),
                'is_done' => $request->input('is_done', 1),
                'user_id' => $userId,
            ]);

            // Vincula a atividade ao Lead na tabela pivot lead_activities
            DB::table('lead_activities')->updateOrInsert([
                'lead_id' => $leadId,
                'activity_id' => $activity->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Atividade / Nota Técnica criada com sucesso!',
                'data' => $activity,
                'id' => $activity->id,
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
