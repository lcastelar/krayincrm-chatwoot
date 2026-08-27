<x-admin::layouts>
    <x-slot:title>
        Integração Chatwoot | Krayin CRM
    </x-slot>

    <!-- Native Krayin CRM Chatwoot Settings Vue Component -->
    <v-chatwoot-settings
        :initial-ping="{{ json_encode($ping) }}"
        :initial-stats="{{ json_encode($stats) }}"
        :initial-logs="{{ json_encode($recentLogs) }}"
        :config="{{ json_encode($config) }}"
        ping-url="{{ route('admin.chatwoot.ping') }}"
        sync-tags-url="{{ route('admin.chatwoot.sync_tags') }}"
        clear-logs-url="{{ route('admin.chatwoot.clear_logs') }}"
        csrf-token="{{ csrf_token() }}"
    >
        <!-- Shimmer Placeholder while loading -->
        <div class="flex flex-col gap-4">
            <div class="flex gap-4 max-xl:flex-wrap">
                <div class="h-28 flex-1 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-28 flex-1 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-28 flex-1 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-28 flex-1 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
            </div>
        </div>
    </v-chatwoot-settings>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-chatwoot-settings-template">
            <div class="flex flex-col gap-5">
                <!-- Header Bar -->
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400">
                            <span class="icon-mail text-2xl"></span>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                                Integração Chatwoot & Krayin CRM
                            </h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Sincronização bidirecional em tempo real de contatos, catálogo de tags e painel de atendimento.
                            </p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="testConnection"
                            :disabled="isPinging"
                            class="secondary-button flex items-center gap-2 text-xs"
                        >
                            <span class="icon-refresh text-sm"></span>
                            <span>@{{ isPinging ? 'Testando...' : 'Testar Conexão' }}</span>
                        </button>

                        <button
                            type="button"
                            @click="syncTags"
                            :disabled="isSyncingTags"
                            class="primary-button flex items-center gap-2 text-xs"
                        >
                            <span class="icon-tag text-sm"></span>
                            <span>@{{ isSyncingTags ? 'Sincronizando...' : 'Sincronizar Catálogo de Tags' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Status Alert Banner -->
                <div v-if="alertMessage" :class="alertClass" class="rounded-lg p-3 text-xs font-medium transition-all shadow-sm">
                    <span v-html="alertMessage"></span>
                </div>

                <!-- 4 KPI Cards Row (Exact Side-by-Side Flex Layout) -->
                <div style="display: flex; gap: 16px; flex-wrap: wrap; width: 100%;">
                    <!-- 1. Status da API -->
                    <div style="flex: 1 1 200px; min-width: 200px;" class="flex flex-col justify-between rounded-lg border border-gray-200 border-l-4 border-l-blue-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">STATUS DA API</span>
                            <span v-if="ping.success" class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                Online
                            </span>
                            <span v-else class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                Desconectado
                            </span>
                        </div>
                        <div class="mt-2 text-lg font-bold text-gray-900 dark:text-white truncate" :title="config.url">
                            @{{ ping.account_name || 'Chatwoot' }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Conta #@{{ config.account_id }} &bull; Token @{{ config.api_token_set ? 'Ativo' : 'Ausente' }}
                        </div>
                    </div>

                    <!-- 2. Catálogo de Tags -->
                    <div style="flex: 1 1 200px; min-width: 200px;" class="flex flex-col justify-between rounded-lg border border-gray-200 border-l-4 border-l-emerald-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">CATÁLOGO DE TAGS</span>
                            <span class="icon-tag text-emerald-600 dark:text-emerald-400"></span>
                        </div>
                        <div class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                            @{{ stats.total_tags }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Etiquetas ativas e espelhadas
                        </div>
                    </div>

                    <!-- 3. Contatos Cadastrados -->
                    <div style="flex: 1 1 200px; min-width: 200px;" class="flex flex-col justify-between rounded-lg border border-gray-200 border-l-4 border-l-purple-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">CONTATOS CADASTRADOS</span>
                            <span class="icon-user text-purple-600 dark:text-purple-400"></span>
                        </div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            @{{ Number(stats.total_persons).toLocaleString('pt-BR') }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @{{ Number(stats.person_tags).toLocaleString('pt-BR') }} tags atribuídas
                        </div>
                    </div>

                    <!-- 4. Eventos Webhook -->
                    <div style="flex: 1 1 200px; min-width: 200px;" class="flex flex-col justify-between rounded-lg border border-gray-200 border-l-4 border-l-amber-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">EVENTOS WEBHOOK</span>
                            <span class="icon-activity text-amber-600 dark:text-amber-400"></span>
                        </div>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            @{{ Number(stats.total_logs).toLocaleString('pt-BR') }}
                        </div>
                        <div class="mt-1 text-xs text-green-600 dark:text-green-400">
                            @{{ stats.recent_success }} sucessos &bull; @{{ stats.recent_failed }} falhas
                        </div>
                    </div>
                </div>

                <!-- 2 Guides Row (Side-by-Side Flex Layout) -->
                <div style="display: flex; gap: 16px; flex-wrap: wrap; width: 100%;">
                    <!-- Webhook Guide -->
                    <div style="flex: 1 1 340px; min-width: 340px;" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
                            <span class="icon-settings text-primary"></span>
                            <span>Webhook de Sincronização em Tempo Real</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Cadastre este endpoint no Chatwoot em <b>Configurações &rarr; Webhooks</b> para sincronizar contatos e tags em tempo real:
                        </p>
                        <div class="mt-3 flex items-center gap-2">
                            <input
                                type="text"
                                readonly
                                :value="config.webhook_url"
                                class="w-full rounded border border-gray-300 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                id="webhook-url-input"
                            />
                            <button
                                type="button"
                                @click="copyInput('webhook-url-input')"
                                class="secondary-button whitespace-nowrap text-xs"
                            >
                                Copiar
                            </button>
                        </div>
                        <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                            <b>Eventos a marcar:</b> Contato criado (<code>contact_created</code>) e Contato atualizado (<code>contact_updated</code>).
                        </div>
                    </div>

                    <!-- Dashboard App Guide -->
                    <div style="flex: 1 1 340px; min-width: 340px;" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
                            <span class="icon-dashboard text-primary"></span>
                            <span>Dashboard App (Barra Lateral do Chatwoot)</span>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Permite aos atendentes visualizarem e criarem leads do Krayin no Chatwoot em <b>Configurações &rarr; Aplicativos do Painel</b>:
                        </p>
                        <div class="mt-3 flex items-center gap-2">
                            <input
                                type="text"
                                readonly
                                :value="config.embed_url"
                                class="w-full rounded border border-gray-300 bg-gray-50 px-2.5 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                id="embed-url-input"
                            />
                            <button
                                type="button"
                                @click="copyInput('embed-url-input')"
                                class="secondary-button whitespace-nowrap text-xs"
                            >
                                Copiar
                            </button>
                        </div>
                        <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                            <b>Nome do aplicativo:</b> Krayin CRM &bull; <b>URL:</b> @{{ config.embed_url }}
                        </div>
                    </div>
                </div>

                <!-- Audit Log Table -->
                <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                        <div class="flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
                            <span class="icon-activity"></span>
                            <span>Log de Auditoria de Eventos e Webhooks</span>
                        </div>

                        <button
                            type="button"
                            @click="clearLogs"
                            class="text-xs text-red-600 hover:underline dark:text-red-400"
                        >
                            Limpar Histórico
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-2.5 font-medium">Data / Hora</th>
                                    <th class="px-4 py-2.5 font-medium">Evento</th>
                                    <th class="px-4 py-2.5 font-medium">Origem</th>
                                    <th class="px-4 py-2.5 font-medium">Status</th>
                                    <th class="px-4 py-2.5 font-medium">Resumo / Ação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                    <td class="whitespace-nowrap px-4 py-2.5 text-gray-600 dark:text-gray-300">
                                        @{{ formatDate(log.created_at) }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2.5 font-semibold text-gray-900 dark:text-white">
                                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] dark:bg-gray-800">
                                            @{{ log.event }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2.5 text-gray-500 uppercase text-[10px]">
                                        @{{ log.source }}
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2.5">
                                        <span v-if="log.status === 'success'" class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                            Sucesso (@{{ log.response_code }})
                                        </span>
                                        <span v-else class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                            Falha (@{{ log.response_code }})
                                        </span>
                                    </td>
                                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                                        @{{ log.summary || 'Sem detalhes' }}
                                        <span v-if="log.error_message" class="block text-[11px] text-red-500">@{{ log.error_message }}</span>
                                    </td>
                                </tr>
                                <tr v-if="logs.length === 0">
                                    <td colspan="5" class="py-6 text-center text-gray-400">
                                        Nenhum evento registrado ainda. Os eventos de webhooks e sincronizações aparecerão aqui automaticamente.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-chatwoot-settings', {
                template: '#v-chatwoot-settings-template',

                props: [
                    'initialPing',
                    'initialStats',
                    'initialLogs',
                    'config',
                    'pingUrl',
                    'syncTagsUrl',
                    'clearLogsUrl',
                    'csrfToken'
                ],

                data() {
                    return {
                        ping: this.initialPing || {},
                        stats: this.initialStats || {},
                        logs: this.initialLogs || [],
                        isPinging: false,
                        isSyncingTags: false,
                        alertMessage: '',
                        alertClass: ''
                    };
                },

                methods: {
                    showAlert(msg, isSuccess = true) {
                        this.alertMessage = msg;
                        this.alertClass = isSuccess
                            ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900/40 dark:text-green-300 dark:border-green-800'
                            : 'bg-red-100 text-red-800 border border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-800';

                        setTimeout(() => {
                            this.alertMessage = '';
                        }, 6000);
                    },

                    async testConnection() {
                        this.isPinging = true;
                        try {
                            const res = await fetch(this.pingUrl, {
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.ping = { success: true, account_name: data.account_name, account_id: data.account_id };
                                this.showAlert(`&check; Conectado com sucesso à conta <b>${data.account_name}</b> (ID #${data.account_id})!`, true);
                            } else {
                                this.ping = { success: false };
                                this.showAlert(`&cross; ${data.message}`, false);
                            }
                        } catch (e) {
                            this.showAlert(`&cross; Erro ao conectar: ${e.message}`, false);
                        } finally {
                            this.isPinging = false;
                        }
                    },

                    async syncTags() {
                        this.isSyncingTags = true;
                        try {
                            const res = await fetch(this.syncTagsUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.stats.total_tags = data.synced_count;
                                this.showAlert(`&check; ${data.message} (${data.synced_count} ativas importadas/atualizadas, ${data.purged_count} obsoletas purgadas).`, true);
                            } else {
                                this.showAlert(`&cross; ${data.message}`, false);
                            }
                        } catch (e) {
                            this.showAlert(`&cross; Erro ao sincronizar tags: ${e.message}`, false);
                        } finally {
                            this.isSyncingTags = false;
                        }
                    },

                    async clearLogs() {
                        if (! confirm('Tem certeza que deseja limpar todos os logs de auditoria?')) return;

                        try {
                            const res = await fetch(this.clearLogsUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': this.csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.logs = [];
                                this.stats.total_logs = 0;
                                this.stats.recent_success = 0;
                                this.stats.recent_failed = 0;
                                this.showAlert('&check; Logs limpos com sucesso.', true);
                            }
                        } catch (e) {
                            this.showAlert(`&cross; Erro ao limpar logs: ${e.message}`, false);
                        }
                    },

                    copyInput(inputId) {
                        const el = document.getElementById(inputId);
                        if (el) {
                            el.select();
                            navigator.clipboard.writeText(el.value);
                            this.showAlert('&check; URL copiada para a área de transferência!', true);
                        }
                    },

                    formatDate(dateStr) {
                        if (! dateStr) return '-';
                        const d = new Date(dateStr);
                        return d.toLocaleString('pt-BR', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit'
                        });
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
