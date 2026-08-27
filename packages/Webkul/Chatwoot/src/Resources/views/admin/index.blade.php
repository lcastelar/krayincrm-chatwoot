<x-admin::layouts>
    <x-slot:title>
        Integração Chatwoot | Krayin CRM
    </x-slot>

    <div class="flex flex-col gap-4">
        {{-- Header --}}
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400">
                    <span class="icon-mail text-2xl"></span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 dark:text-white">Integração Chatwoot & Krayin CRM</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Sincronização bidirecional em tempo real de contatos, tags e painel de atendimento.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    onclick="testConnection()"
                    id="btn-ping"
                    class="secondary-button flex items-center gap-2 text-xs"
                >
                    <span class="icon-refresh text-sm"></span>
                    <span id="ping-text">Testar Conexão</span>
                </button>

                <button
                    type="button"
                    onclick="syncTags()"
                    id="btn-sync-tags"
                    class="primary-button flex items-center gap-2 text-xs"
                >
                    <span class="icon-tag text-sm"></span>
                    <span id="sync-tags-text">Sincronizar Catálogo de Tags</span>
                </button>
            </div>
        </div>

        {{-- Status Notification Alert --}}
        <div id="status-alert" class="hidden rounded-lg p-3 text-xs font-medium transition-all"></div>

        {{-- KPI Cards Grid --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Status de Conexão --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">STATUS DA API</span>
                    @if ($ping['success'] ?? false)
                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/50 dark:text-green-300">
                            Online
                        </span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/50 dark:text-red-300">
                            Desconectado
                        </span>
                    @endif
                </div>
                <div class="mt-2 text-lg font-bold text-gray-800 dark:text-white truncate" title="{{ $config['url'] }}">
                    {{ $ping['account_name'] ?? 'Chatwoot' }}
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Conta #{{ $config['account_id'] }} &bull; Token {{ $config['api_token_set'] ? 'Ativo' : 'Ausente' }}
                </div>
            </div>

            {{-- Total de Tags Ativas --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">CATÁLOGO DE TAGS</span>
                    <span class="icon-tag text-gray-400"></span>
                </div>
                <div class="mt-2 text-2xl font-bold text-gray-800 dark:text-white" id="stat-total-tags">
                    {{ $stats['total_tags'] }}
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Etiquetas ativas e espelhadas
                </div>
            </div>

            {{-- Contatos com Tags --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">CONTATOS CADASTRADOS</span>
                    <span class="icon-user text-gray-400"></span>
                </div>
                <div class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">
                    {{ number_format($stats['total_persons'], 0, ',', '.') }}
                </div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ number_format($stats['person_tags'], 0, ',', '.') }} tags atribuídas
                </div>
            </div>

            {{-- Eventos Webhook Recebidos --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">EVENTOS WEBHOOK</span>
                    <span class="icon-activity text-gray-400"></span>
                </div>
                <div class="mt-2 text-2xl font-bold text-gray-800 dark:text-white">
                    {{ number_format($stats['total_logs'], 0, ',', '.') }}
                </div>
                <div class="mt-1 text-xs text-green-600 dark:text-green-400">
                    {{ $stats['recent_success'] }} sucessos &bull; {{ $stats['recent_failed'] }} falhas
                </div>
            </div>
        </div>

        {{-- Configuration Endpoints & Guides --}}
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            {{-- Webhook Guide --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 font-semibold text-gray-800 dark:text-white">
                    <span class="icon-settings text-primary"></span>
                    <span>Webhook de Sincronização em Tempo Real</span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Cadastre este endpoint no Chatwoot em <b>Configurações &rarr; Webhooks</b> para receber alterações de contatos e tags instantaneamente:
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <input
                        type="text"
                        readonly
                        value="{{ $config['webhook_url'] }}"
                        class="w-full rounded border border-gray-300 bg-gray-50 px-2 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                        id="webhook-url-input"
                    />
                    <button
                        type="button"
                        onclick="copyToClipboard('webhook-url-input')"
                        class="secondary-button whitespace-nowrap text-xs"
                    >
                        Copiar
                    </button>
                </div>
                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                    <b>Eventos recomendados:</b> contact_created, contact_updated, contact_deleted, label_created, label_updated, label_deleted.
                </div>
            </div>

            {{-- Dashboard App Guide --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 font-semibold text-gray-800 dark:text-white">
                    <span class="icon-dashboard text-primary"></span>
                    <span>Dashboard App (Barra Lateral do Chatwoot)</span>
                </div>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Permite aos atendentes visualizarem e criarem leads do Krayin dentro do Chatwoot em <b>Configurações &rarr; Aplicativos do Painel</b>:
                </p>
                <div class="mt-3 flex items-center gap-2">
                    <input
                        type="text"
                        readonly
                        value="{{ $config['embed_url'] }}"
                        class="w-full rounded border border-gray-300 bg-gray-50 px-2 py-1.5 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300"
                        id="embed-url-input"
                    />
                    <button
                        type="button"
                        onclick="copyToClipboard('embed-url-input')"
                        class="secondary-button whitespace-nowrap text-xs"
                    >
                        Copiar
                    </button>
                </div>
                <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                    <b>Nome do aplicativo:</b> Krayin CRM &bull; <b>URL:</b> {{ $config['embed_url'] }}
                </div>
            </div>
        </div>

        {{-- Audit Log Table --}}
        <div class="rounded-lg border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-800">
                <div class="flex items-center gap-2 font-semibold text-gray-800 dark:text-white">
                    <span class="icon-activity"></span>
                    <span>Log de Auditoria de Eventos e Webhooks</span>
                </div>

                <button
                    type="button"
                    onclick="clearLogs()"
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
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800" id="logs-table-body">
                        @forelse ($recentLogs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                                <td class="whitespace-nowrap px-4 py-2.5 text-gray-600 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2.5 font-semibold text-gray-800 dark:text-white">
                                    <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[11px] dark:bg-gray-800">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-2.5 text-gray-500 uppercase text-[10px]">
                                    {{ $log->source }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2.5">
                                    @if ($log->status === 'success')
                                        <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-semibold text-green-800 dark:bg-green-900/50 dark:text-green-300">
                                            Sucesso ({{ $log->response_code }})
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-semibold text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                            Falha ({{ $log->response_code }})
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">
                                    {{ $log->summary ?? 'Sem detalhes' }}
                                    @if (! empty($log->error_message))
                                        <span class="block text-[11px] text-red-500">{{ $log->error_message }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-400">
                                    Nenhum evento registrado ainda. Os eventos de webhooks e sincronizações aparecerão aqui automaticamente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Script de Interatividade --}}
    <script>
        function showAlert(msg, isSuccess = true) {
            const alert = document.getElementById('status-alert');
            alert.className = `rounded-lg p-3 text-xs font-medium transition-all ${
                isSuccess
                    ? 'bg-green-100 text-green-800 border border-green-200 dark:bg-green-900/40 dark:text-green-300 dark:border-green-800'
                    : 'bg-red-100 text-red-800 border border-red-200 dark:bg-red-900/40 dark:text-red-300 dark:border-red-800'
            }`;
            alert.innerHTML = msg;
            alert.classList.remove('hidden');
            setTimeout(() => alert.classList.add('hidden'), 6000);
        }

        async function testConnection() {
            const btn = document.getElementById('btn-ping');
            const txt = document.getElementById('ping-text');
            txt.innerText = 'Testando...';
            btn.disabled = true;

            try {
                const res = await fetch("{{ route('admin.chatwoot.ping') }}", {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    showAlert(`&check; Conectado com sucesso à conta <b>${data.account_name}</b> (ID #${data.account_id})!`, true);
                } else {
                    showAlert(`&cross; ${data.message}`, false);
                }
            } catch (e) {
                showAlert(`&cross; Erro ao conectar: ${e.message}`, false);
            } finally {
                txt.innerText = 'Testar Conexão';
                btn.disabled = false;
            }
        }

        async function syncTags() {
            const btn = document.getElementById('btn-sync-tags');
            const txt = document.getElementById('sync-tags-text');
            txt.innerText = 'Sincronizando...';
            btn.disabled = true;

            try {
                const res = await fetch("{{ route('admin.chatwoot.sync_tags') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    showAlert(`&check; ${data.message} (${data.synced_count} ativas importadas/atualizadas, ${data.purged_count} obsoletas purgadas).`, true);
                    const tagStat = document.getElementById('stat-total-tags');
                    if (tagStat) tagStat.innerText = data.synced_count;
                } else {
                    showAlert(`&cross; ${data.message}`, false);
                }
            } catch (e) {
                showAlert(`&cross; Erro ao sincronizar tags: ${e.message}`, false);
            } finally {
                txt.innerText = 'Sincronizar Catálogo de Tags';
                btn.disabled = false;
            }
        }

        async function clearLogs() {
            if (! confirm('Tem certeza que deseja limpar todos os logs de auditoria?')) return;

            try {
                const res = await fetch("{{ route('admin.chatwoot.clear_logs') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    showAlert('&check; Logs limpos com sucesso.', true);
                    document.getElementById('logs-table-body').innerHTML = `
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-400">
                                Nenhum evento registrado ainda.
                            </td>
                        </tr>
                    `;
                }
            } catch (e) {
                showAlert(`&cross; Erro ao limpar logs: ${e.message}`, false);
            }
        }

        function copyToClipboard(inputId) {
            const input = document.getElementById(inputId);
            input.select();
            navigator.clipboard.writeText(input.value);
            showAlert('&check; URL copiada para a área de transferência!', true);
        }
    </script>
</x-admin::layouts>
