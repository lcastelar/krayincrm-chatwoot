<?php

namespace Webkul\Reports\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Activity\Models\Activity;
use Webkul\Lead\Models\Lead;
use Webkul\Lead\Models\Pipeline;
use Webkul\Lead\Models\Stage;
use Webkul\Tag\Models\Tag;
use Webkul\User\Models\User;

class ReportController extends Controller
{
    /**
     * Display reports dashboard.
     */
    public function index(Request $request)
    {
        $pipelines = Pipeline::with('stages')->get();
        $users = User::all();
        $tags = Tag::all();

        $filterData = $this->getFilteredReportData($request);

        return view('reports::index', array_merge([
            'pipelines' => $pipelines,
            'users'     => $users,
            'tags'      => $tags,
        ], $filterData));
    }

    /**
     * API endpoint to get JSON report data.
     */
    public function getData(Request $request)
    {
        return response()->json($this->getFilteredReportData($request));
    }

    /**
     * Export raw report data to CSV.
     */
    public function exportCsv(Request $request)
    {
        $data = $this->getFilteredReportData($request);

        $filename = 'relatorio-crm-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            // BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Summary row
            fputcsv($file, ['Resumo de Métricas', '']);
            fputcsv($file, ['Receita Total Ganha (R$)', number_format($data['metrics']['total_won_revenue'], 2, ',', '.')]);
            fputcsv($file, ['Ticket Médio (R$)', number_format($data['metrics']['average_ticket'], 2, ',', '.')]);
            fputcsv($file, ['Taxa de Conversão (Win Rate)', number_format($data['metrics']['win_rate'], 1, ',', '.') . '%']);
            fputcsv($file, ['Total de Oportunidades', $data['metrics']['total_leads']]);
            fputcsv($file, ['Negócios Ganhos', $data['metrics']['won_leads']]);
            fputcsv($file, ['Negócios Perdidos', $data['metrics']['lost_leads']]);
            fputcsv($file, ['Ciclo Médio de Venda (dias)', $data['metrics']['avg_sales_cycle_days']]);
            fputcsv($file, []);

            // Team ranking
            fputcsv($file, ['Desempenho por Vendedor', '', '', '', '']);
            fputcsv($file, ['Vendedor', 'Total Negócios', 'Ganhos', 'Perdidos', 'Conversão (%)', 'Receita Ganha (R$)']);
            foreach ($data['team_ranking'] as $rep) {
                fputcsv($file, [
                    $rep['name'],
                    $rep['total'],
                    $rep['won'],
                    $rep['lost'],
                    number_format($rep['win_rate'], 1, ',', '.') . '%',
                    number_format($rep['revenue'], 2, ',', '.')
                ]);
            }
            fputcsv($file, []);

            // Tags performance
            fputcsv($file, ['Desempenho por Tag', '', '', '']);
            fputcsv($file, ['Tag', 'Total Leads', 'Ganhos', 'Receita Ganha (R$)']);
            foreach ($data['tags_performance'] as $tag) {
                fputcsv($file, [
                    $tag['name'],
                    $tag['leads_count'],
                    $tag['won_count'],
                    number_format($tag['revenue'], 2, ',', '.')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Compute filtered report data.
     */
    protected function getFilteredReportData(Request $request): array
    {
        $period = $request->get('period', 'this_month');
        $customStart = $request->get('start_date');
        $customEnd = $request->get('end_date');
        $pipelineId = $request->get('pipeline_id');
        $userId = $request->get('user_id');

        // Resolve date range
        [$startDate, $endDate, $periodLabel] = $this->resolveDateRange($period, $customStart, $customEnd);

        // Base lead query
        $leadsQuery = DB::table('leads')
            ->leftJoin('lead_pipelines', 'leads.lead_pipeline_id', '=', 'lead_pipelines.id')
            ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
            ->whereBetween('leads.created_at', [$startDate, $endDate]);

        if ($pipelineId) {
            $leadsQuery->where('leads.lead_pipeline_id', $pipelineId);
        }

        if ($userId) {
            $leadsQuery->where('leads.user_id', $userId);
        }

        // Summary metrics
        $allLeads = (clone $leadsQuery)->select(
            'leads.id',
            'leads.title',
            'leads.lead_value',
            'leads.status',
            'leads.user_id',
            'leads.person_id',
            'leads.created_at',
            'leads.closed_at',
            'leads.updated_at',
            'leads.lead_pipeline_id',
            'leads.lead_pipeline_stage_id',
            'lead_pipeline_stages.code as stage_code',
            'lead_pipeline_stages.name as stage_name'
        )->get();

        $totalLeads = $allLeads->count();
        
        $wonLeads = $allLeads->filter(function ($lead) {
            return $lead->status === 'won' || $lead->stage_code === 'won' || str_contains(strtolower($lead->stage_name ?? ''), 'ganh');
        });

        $lostLeads = $allLeads->filter(function ($lead) {
            return $lead->status === 'lost' || $lead->stage_code === 'lost' || str_contains(strtolower($lead->stage_name ?? ''), 'perdid');
        });

        $openLeads = $allLeads->reject(function ($lead) use ($wonLeads, $lostLeads) {
            return $wonLeads->contains('id', $lead->id) || $lostLeads->contains('id', $lead->id);
        });

        $totalWonRevenue = (float) $wonLeads->sum('lead_value');
        $totalOpenPipelineValue = (float) $openLeads->sum('lead_value');
        $averageTicket = $wonLeads->count() > 0 ? ($totalWonRevenue / $wonLeads->count()) : 0;
        
        $closedCount = $wonLeads->count() + $lostLeads->count();
        $winRate = $closedCount > 0 ? (($wonLeads->count() / $closedCount) * 100) : 0;

        // Sales Cycle (average days to close won deals)
        $salesCycleDays = 0;
        if ($wonLeads->count() > 0) {
            $totalDays = 0;
            foreach ($wonLeads as $wLead) {
                $created = Carbon::parse($wLead->created_at);
                $closed = $wLead->closed_at ? Carbon::parse($wLead->closed_at) : Carbon::parse($wLead->updated_at);
                $diff = max(0, $created->diffInDays($closed));
                $totalDays += $diff;
            }
            $salesCycleDays = round($totalDays / $wonLeads->count(), 1);
        }

        // Timeline Data for Chart (Revenue & Deals by Day or Month)
        $timeline = $this->buildTimelineData($allLeads, $startDate, $endDate);

        // Funnel / Pipeline Stages Data
        $funnelData = $this->buildFunnelData($pipelineId, $allLeads, $wonLeads, $lostLeads);

        // Tags Performance Data
        $tagsPerformance = $this->buildTagsPerformance($startDate, $endDate, $pipelineId, $userId);

        // Sales Team Ranking
        $teamRanking = $this->buildTeamRanking($allLeads);

        // Activities count in period
        $activitiesQuery = DB::table('activities')
            ->whereBetween('created_at', [$startDate, $endDate]);
        if ($userId) {
            $activitiesQuery->where('user_id', $userId);
        }

        $activitiesCount = [
            'total'   => (clone $activitiesQuery)->count(),
            'notes'   => (clone $activitiesQuery)->where('type', 'note')->count(),
            'calls'   => (clone $activitiesQuery)->where('type', 'call')->count(),
            'emails'  => (clone $activitiesQuery)->where('type', 'email')->count(),
            'tasks'   => (clone $activitiesQuery)->whereIn('type', ['task', 'meeting'])->count(),
        ];

        return [
            'filters' => [
                'period'       => $period,
                'start_date'   => $startDate->format('Y-m-d'),
                'end_date'     => $endDate->format('Y-m-d'),
                'period_label' => $periodLabel,
                'pipeline_id'  => $pipelineId,
                'user_id'      => $userId,
            ],
            'metrics' => [
                'total_leads'            => $totalLeads,
                'won_leads'              => $wonLeads->count(),
                'lost_leads'             => $lostLeads->count(),
                'open_leads'             => $openLeads->count(),
                'total_won_revenue'      => $totalWonRevenue,
                'average_ticket'         => $averageTicket,
                'open_pipeline_value'    => $totalOpenPipelineValue,
                'win_rate'               => $winRate,
                'avg_sales_cycle_days'   => $salesCycleDays,
            ],
            'timeline'         => $timeline,
            'funnel'           => $funnelData,
            'tags_performance' => $tagsPerformance,
            'team_ranking'     => $teamRanking,
            'activities'       => $activitiesCount,
        ];
    }

    /**
     * Resolve start and end dates from period preset.
     */
    protected function resolveDateRange(string $period, ?string $customStart, ?string $customEnd): array
    {
        $now = Carbon::now();

        switch ($period) {
            case 'today':
                return [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Hoje'];
            case 'yesterday':
                $y = $now->copy()->subDay();
                return [$y->copy()->startOfDay(), $y->copy()->endOfDay(), 'Ontem'];
            case 'last_7_days':
                return [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), 'Últimos 7 dias'];
            case 'last_30_days':
                return [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay(), 'Últimos 30 dias'];
            case 'last_month':
                $lm = $now->copy()->subMonth();
                return [$lm->copy()->startOfMonth(), $lm->copy()->endOfMonth(), 'Mês Passado'];
            case 'this_year':
                return [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'Este Ano'];
            case 'custom':
                if ($customStart && $customEnd) {
                    return [
                        Carbon::parse($customStart)->startOfDay(),
                        Carbon::parse($customEnd)->endOfDay(),
                        Carbon::parse($customStart)->format('d/m/Y') . ' até ' . Carbon::parse($customEnd)->format('d/m/Y')
                    ];
                }
                // Fallthrough to this_month
            case 'this_month':
            default:
                return [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'Este Mês'];
        }
    }

    /**
     * Build timeline chart data.
     */
    protected function buildTimelineData($allLeads, Carbon $startDate, Carbon $endDate): array
    {
        $daysDiff = $startDate->diffInDays($endDate);
        $format = $daysDiff > 60 ? 'Y-m' : 'Y-m-d';
        $displayFormat = $daysDiff > 60 ? 'M/Y' : 'd/m';

        $timelinePoints = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $key = $current->format($format);
            $label = $current->format($displayFormat);
            if (!isset($timelinePoints[$key])) {
                $timelinePoints[$key] = [
                    'label'       => $label,
                    'revenue_won' => 0.0,
                    'leads_count' => 0,
                    'won_count'   => 0,
                ];
            }
            if ($daysDiff > 60) {
                $current->addMonth();
            } else {
                $current->addDay();
            }
        }

        foreach ($allLeads as $lead) {
            $created = Carbon::parse($lead->created_at);
            $key = $created->format($format);
            if (isset($timelinePoints[$key])) {
                $timelinePoints[$key]['leads_count']++;
                $isWon = $lead->status === 'won' || $lead->stage_code === 'won' || str_contains(strtolower($lead->stage_name ?? ''), 'ganh');
                if ($isWon) {
                    $timelinePoints[$key]['won_count']++;
                    $timelinePoints[$key]['revenue_won'] += (float)$lead->lead_value;
                }
            }
        }

        return [
            'labels'      => array_column($timelinePoints, 'label'),
            'revenue_won' => array_column($timelinePoints, 'revenue_won'),
            'leads_count' => array_column($timelinePoints, 'leads_count'),
            'won_count'   => array_column($timelinePoints, 'won_count'),
        ];
    }

    /**
     * Build funnel stage metrics.
     */
    protected function buildFunnelData(?int $pipelineId, $allLeads, $wonLeads, $lostLeads): array
    {
        $pipeline = $pipelineId ? Pipeline::with('stages')->find($pipelineId) : Pipeline::with('stages')->first();
        if (!$pipeline) {
            return ['stages' => [], 'pipeline_name' => 'Padrão'];
        }

        $stagesData = [];
        $totalInPipeline = $allLeads->where('lead_pipeline_id', $pipeline->id)->count();

        foreach ($pipeline->stages as $stage) {
            $leadsInStage = $allLeads->where('lead_pipeline_stage_id', $stage->id);
            $count = $leadsInStage->count();
            $value = (float) $leadsInStage->sum('lead_value');
            $pct = $totalInPipeline > 0 ? round(($count / $totalInPipeline) * 100, 1) : 0;

            $stagesData[] = [
                'id'         => $stage->id,
                'name'       => $stage->name,
                'code'       => $stage->code,
                'count'      => $count,
                'value'      => $value,
                'percentage' => $pct,
            ];
        }

        return [
            'pipeline_name' => $pipeline->name,
            'total_leads'   => $totalInPipeline,
            'stages'        => $stagesData,
        ];
    }

    /**
     * Build performance metrics by Contact Tags.
     */
    protected function buildTagsPerformance(Carbon $startDate, Carbon $endDate, ?int $pipelineId, ?int $userId): array
    {
        $tagsQuery = DB::table('tags')
            ->join('person_tags', 'tags.id', '=', 'person_tags.tag_id')
            ->join('leads', 'person_tags.person_id', '=', 'leads.person_id')
            ->leftJoin('lead_pipeline_stages', 'leads.lead_pipeline_stage_id', '=', 'lead_pipeline_stages.id')
            ->whereBetween('leads.created_at', [$startDate, $endDate])
            ->select(
                'tags.id',
                'tags.name',
                DB::raw('COUNT(DISTINCT leads.id) as leads_count'),
                DB::raw("SUM(CASE WHEN leads.status = 'won' OR lead_pipeline_stages.code = 'won' THEN 1 ELSE 0 END) as won_count"),
                DB::raw("SUM(CASE WHEN leads.status = 'won' OR lead_pipeline_stages.code = 'won' THEN leads.lead_value ELSE 0 END) as revenue")
            )
            ->groupBy('tags.id', 'tags.name')
            ->orderByDesc('revenue')
            ->limit(15);

        if ($pipelineId) {
            $tagsQuery->where('leads.lead_pipeline_id', $pipelineId);
        }

        if ($userId) {
            $tagsQuery->where('leads.user_id', $userId);
        }

        $results = $tagsQuery->get();

        return $results->map(function ($row) {
            $won = (int)$row->won_count;
            $total = (int)$row->leads_count;
            return [
                'id'          => $row->id,
                'name'        => $row->name,
                'leads_count' => $total,
                'won_count'   => $won,
                'win_rate'    => $total > 0 ? round(($won / $total) * 100, 1) : 0,
                'revenue'     => (float)$row->revenue,
            ];
        })->toArray();
    }

    /**
     * Build sales team performance ranking.
     */
    protected function buildTeamRanking($allLeads): array
    {
        $users = User::all()->keyBy('id');
        $ranking = [];

        $groupedByUser = $allLeads->groupBy('user_id');

        foreach ($groupedByUser as $userId => $leads) {
            $user = $users->get($userId);
            $userName = $user ? $user->name : 'Não Atribuído';

            $total = $leads->count();
            $won = $leads->filter(function ($l) {
                return $l->status === 'won' || $l->stage_code === 'won' || str_contains(strtolower($l->stage_name ?? ''), 'ganh');
            });
            $lost = $leads->filter(function ($l) {
                return $l->status === 'lost' || $l->stage_code === 'lost' || str_contains(strtolower($l->stage_name ?? ''), 'perdid');
            });

            $revenue = (float)$won->sum('lead_value');
            $closed = $won->count() + $lost->count();
            $winRate = $closed > 0 ? round(($won->count() / $closed) * 100, 1) : 0;

            $ranking[] = [
                'user_id'  => $userId,
                'name'     => $userName,
                'total'    => $total,
                'won'      => $won->count(),
                'lost'     => $lost->count(),
                'win_rate' => $winRate,
                'revenue'  => $revenue,
            ];
        }

        usort($ranking, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });

        return $ranking;
    }
}
