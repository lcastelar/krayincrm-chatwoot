<?php

namespace Webkul\Chatwoot\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Webkul\Chatwoot\Services\ChatwootApiService;
use Webkul\Chatwoot\Services\SyncContext;

class ChatwootAdminController extends Controller
{
    public function __construct(
        protected ChatwootApiService $chatwootApi
    ) {}

    /**
     * Display the Chatwoot Integration Admin Panel.
     */
    public function index()
    {
        $ping = $this->chatwootApi->ping();

        $stats = [
            'total_tags'     => DB::table('tags')->count(),
            'total_persons'  => DB::table('persons')->count(),
            'person_tags'    => DB::table('person_tags')->count(),
            'total_logs'     => DB::table('chatwoot_webhook_logs')->count(),
            'recent_success' => DB::table('chatwoot_webhook_logs')->where('status', 'success')->count(),
            'recent_failed'  => DB::table('chatwoot_webhook_logs')->where('status', 'failed')->count(),
        ];

        $recentLogs = DB::table('chatwoot_webhook_logs')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $config = [
            'url'            => config('chatwoot.url', env('CHATWOOT_URL', 'https://chat.dmarelectronics.com')),
            'account_id'     => config('chatwoot.account_id', env('CHATWOOT_ACCOUNT_ID', 1)),
            'api_token_set'  => ! empty(config('chatwoot.api_token', env('CHATWOOT_API_TOKEN'))),
            'webhook_url'    => url('/api/chatwoot/webhook'),
            'embed_url'      => url('/chatwoot/embed'),
        ];

        return view('chatwoot::admin.index', compact('ping', 'stats', 'recentLogs', 'config'));
    }

    /**
     * AJAX: Test connection with Chatwoot API.
     */
    public function ping()
    {
        $result = $this->chatwootApi->ping();
        return response()->json($result);
    }

    /**
     * AJAX: Sincronizar e alinhar catálogo de tags com o Chatwoot (Purga obsoletas e espelha ativas).
     */
    public function syncTagsNow()
    {
        try {
            $labels = $this->chatwootApi->getLabels();

            if (empty($labels)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma etiqueta encontrada no Chatwoot ou falha ao consultar API.',
                ], 400);
            }

            $activeTitles = [];

            // 1. Inserir ou atualizar cada etiqueta ativa
            foreach ($labels as $label) {
                $title = trim($label['title']);
                $color = $label['color'] ?? '#51C984';
                $activeTitles[] = $title;

                $existing = DB::table('tags')->where('name', $title)->first();
                if ($existing) {
                    DB::table('tags')->where('id', $existing->id)->update([
                        'color'      => $color,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('tags')->insert([
                        'name'       => $title,
                        'color'      => $color,
                        'user_id'    => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 2. Purgar tags obsoletas do Krayin que não existem no Chatwoot
            $obsoleteTags = DB::table('tags')->whereNotIn('name', $activeTitles)->get();
            $purgedCount = $obsoleteTags->count();

            if ($purgedCount > 0) {
                $obsoleteIds = $obsoleteTags->pluck('id')->toArray();
                DB::table('person_tags')->whereIn('tag_id', $obsoleteIds)->delete();
                DB::table('lead_tags')->whereIn('tag_id', $obsoleteIds)->delete();
                DB::table('tags')->whereIn('id', $obsoleteIds)->delete();
            }

            return response()->json([
                'success'       => true,
                'synced_count'  => count($activeTitles),
                'purged_count'  => $purgedCount,
                'active_labels' => $activeTitles,
                'message'       => 'Catálogo de tags 100% alinhado com o Chatwoot!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao sincronizar tags: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX: Limpar logs antigos de auditoria.
     */
    public function clearLogs()
    {
        try {
            DB::table('chatwoot_webhook_logs')->truncate();
            return response()->json(['success' => true, 'message' => 'Logs de auditoria limpos com sucesso.']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
