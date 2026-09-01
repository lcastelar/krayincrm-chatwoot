<?php

namespace Webkul\Chatwoot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\StageRepository;
use Webkul\Activity\Repositories\ActivityRepository;

class ChatwootEmbedController extends Controller
{
    public function __construct(
        protected PersonRepository $personRepository,
        protected LeadRepository $leadRepository,
        protected PipelineRepository $pipelineRepository,
        protected StageRepository $stageRepository,
        protected ActivityRepository $activityRepository
    ) {}

    /**
     * Display the embedded host view for Chatwoot Dashboard App iframe.
     */
    public function index(Request $request)
    {
        $token = $request->get('token');
        $expectedSecret = env('CHATWOOT_EMBED_SECRET', '');

        if (! empty($expectedSecret) && $token !== $expectedSecret) {
            return response()->view('chatwoot::unauthorized', [], 401);
        }

        $currentUser = auth()->guard('user')->user();

        return view('chatwoot::embed', [
            'token'       => $token,
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * Search contact by chatwoot appContext data and return rich data for embed.
     */
    public function search(Request $request)
    {
        $chatwootContact = $request->input('contact', []);
        $email = $chatwootContact['email'] ?? null;
        $phone = $chatwootContact['phone_number'] ?? null;

        $person = null;

        if ($email) {
            $person = $this->personRepository->scopeQuery(function ($query) use ($email) {
                return $query->where('emails', 'like', "%{$email}%");
            })->first();
        }

        if (! $person && $phone) {
            $cleanedPhone = preg_replace('/[^\d]/', '', $phone);
            $person = $this->personRepository->scopeQuery(function ($query) use ($phone, $cleanedPhone) {
                return $query->where('contact_numbers', 'like', "%{$phone}%")
                             ->orWhere('contact_numbers', 'like', "%{$cleanedPhone}%");
            })->first();
        }

        $allPipelines = $this->pipelineRepository->all()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'stages' => $p->stages->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name,
                    'code' => $s->code,
                    'sort_order' => $s->sort_order,
                ]),
            ];
        });
        $sources = DB::table('lead_sources')->orderBy('name')->get(['id', 'name']);

        if (! $person) {
            return response()->json([
                'found' => false,
                'chatwoot_contact' => $chatwootContact,
                'pipelines' => $allPipelines,
                'sources' => $sources,
            ]);
        }

        $leads = $this->leadRepository->findWhere(['person_id' => $person->id]);

        $formattedLeads = $leads->map(function ($lead) {
            $pipeline = $lead->pipeline;
            $stages = $pipeline ? $pipeline->stages->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'code' => $s->code,
                'sort_order' => $s->sort_order,
            ]) : [];

            // Get notes for this specific lead
            $notes = $lead->activities()
                ->where('type', 'note')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'comment' => strip_tags($activity->comment),
                        'user_name' => $activity->user?->name ?? 'Sistema',
                        'created_at' => $activity->created_at ? $activity->created_at->format('d/m/Y H:i') : '',
                    ];
                });

            $isLost = ($lead->stage && ($lead->stage->code === 'lost' || str_contains(strtolower($lead->stage->name), 'perdid'))) || (strtolower($lead->status ?? '') === 'lost');
            $isWon = ($lead->stage && ($lead->stage->code === 'won' || str_contains(strtolower($lead->stage->name), 'ganh'))) || (strtolower($lead->status ?? '') === 'won');

            return [
                'id' => $lead->id,
                'title' => $lead->title,
                'lead_value' => floatval($lead->lead_value ?? 0),
                'lead_value_formatted' => number_format(floatval($lead->lead_value ?? 0), 2, ',', '.'),
                'status' => $lead->status,
                'pipeline_id' => $lead->lead_pipeline_id,
                'pipeline_name' => $pipeline?->name ?? 'Funil',
                'stage_id' => $lead->lead_pipeline_stage_id,
                'stage_name' => $lead->stage?->name ?? '',
                'stage_code' => $lead->stage?->code ?? '',
                'is_lost' => $isLost,
                'is_won' => $isWon,
                'stages' => $stages,
                'notes' => $notes,
                'created_at' => $lead->created_at ? $lead->created_at->format('d/m/Y H:i') : '',
                'view_url' => url("/admin/leads/view/{$lead->id}"),
            ];
        });

        // Load unique person tags
        $tags = [];
        if ($person->tags) {
            $tags = $person->tags->pluck('name')->filter()->unique()->values()->toArray();
        }

        return response()->json([
            'found' => true,
            'person' => [
                'id' => $person->id,
                'name' => $person->name,
                'emails' => is_array($person->emails) ? $person->emails : [],
                'contact_numbers' => is_array($person->contact_numbers) ? $person->contact_numbers : [],
                'job_title' => $person->job_title,
                'tags' => $tags,
                'view_url' => url("/admin/contacts/persons/view/{$person->id}"),
            ],
            'leads' => $formattedLeads,
            'pipelines' => $allPipelines,
            'sources' => $sources,
        ]);
    }

    /**
     * Create Person and/or Lead from Chatwoot iframe.
     */
    public function storeLead(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'pipeline_id' => 'required|integer',
            'stage_id' => 'required|integer',
            'lead_source_id' => 'required|integer|exists:lead_sources,id',
            'lead_value' => 'nullable|numeric',
            'person_id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        try {
            $personId = $request->input('person_id');

            $authUserId = auth()->guard('user')->id() ?? config('chatwoot.default_user_id', 1);

            if (! $personId) {
                $personData = [
                    'name' => $request->input('name') ?: 'Novo Contato',
                    'emails' => $request->input('email') ? [['value' => $request->input('email'), 'label' => 'work']] : [],
                    'contact_numbers' => $request->input('phone') ? [['value' => $request->input('phone'), 'label' => 'work']] : [],
                    'entity_type' => 'persons',
                    'user_id' => $authUserId,
                ];

                $person = $this->personRepository->create($personData);
                $personId = $person->id;
            }

            $leadData = [
                'title' => $request->input('title'),
                'lead_value' => $request->input('lead_value', 0),
                'person_id' => $personId,
                'lead_pipeline_id' => $request->input('pipeline_id'),
                'lead_pipeline_stage_id' => $request->input('stage_id'),
                'lead_source_id' => $request->input('lead_source_id'),
                'lead_type_id' => 1,   // Novo Negócio
                'user_id' => $authUserId,
                'entity_type' => 'leads',
            ];

            $lead = $this->leadRepository->create($leadData);

            return response()->json([
                'success' => true,
                'message' => 'Lead criado com sucesso no Krayin!',
                'lead_id' => $lead->id,
                'view_url' => url("/admin/leads/view/{$lead->id}"),
            ]);
        } catch (\Exception $e) {
            Log::error('ChatwootEmbedController@storeLead error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar Lead: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update Lead Pipeline Stage.
     */
    public function updateStage(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|integer',
            'stage_id' => 'required|integer',
        ]);

        try {
            $stage = $this->stageRepository->find($request->input('stage_id'));
            
            $status = null;
            if ($stage) {
                if ($stage->code === 'won') {
                    $status = 'won';
                } elseif ($stage->code === 'lost') {
                    $status = 'lost';
                }
            }

            $updateData = [
                'lead_pipeline_stage_id' => $request->input('stage_id'),
                'entity_type' => 'leads',
            ];

            if ($status) {
                $updateData['status'] = $status;
            }

            $lead = $this->leadRepository->update($updateData, $request->input('lead_id'));

            return response()->json([
                'success' => true,
                'message' => 'Estágio do Lead atualizado!',
                'stage_id' => $lead->lead_pipeline_stage_id,
                'stage_name' => $lead->stage?->name,
                'stage_code' => $lead->stage?->code,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar estágio: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add Note/Activity to a specific Lead.
     */
    public function storeActivity(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|integer',
            'comment' => 'required|string',
        ]);

        try {
            $authUserId = auth()->guard('user')->id() ?? config('chatwoot.default_user_id', 1);

            $activity = $this->activityRepository->create([
                'type' => 'note',
                'title' => 'Nota via Chatwoot',
                'comment' => $request->input('comment'),
                'lead_id' => $request->input('lead_id'),
                'is_done' => 1,
                'user_id' => $authUserId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nota adicionada com sucesso ao negócio!',
                'activity' => [
                    'id' => $activity->id,
                    'comment' => strip_tags($activity->comment),
                    'user_name' => $activity->user?->name ?? 'Você',
                    'created_at' => $activity->created_at->format('d/m/Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar nota: ' . $e->getMessage(),
            ], 500);
        }
    }
}
