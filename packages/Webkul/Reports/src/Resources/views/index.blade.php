<x-admin::layouts>
    <x-slot:title>
        Relatórios & Inteligência de Vendas
    </x-slot>

    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Native Krayin CRM Reports Vue Component -->
    <v-reports-dashboard
        :initial-metrics="{{ json_encode($metrics) }}"
        :initial-filters="{{ json_encode($filters) }}"
        :initial-timeline="{{ json_encode($timeline) }}"
        :initial-funnel="{{ json_encode($funnel) }}"
        :initial-tags="{{ json_encode($tags_performance) }}"
        :initial-team="{{ json_encode($team_ranking) }}"
        :initial-activities="{{ json_encode($activities) }}"
        :pipelines="{{ json_encode($pipelines) }}"
        :users="{{ json_encode($users) }}"
        export-url="{{ route('admin.reports.export') }}"
        filter-url="{{ route('admin.reports.index') }}"
    >
        <!-- Shimmer Placeholder while loading -->
        <div class="flex flex-col gap-4">
            <div class="flex gap-4 max-xl:flex-wrap">
                <div class="h-28 flex-1 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-28 flex-1 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-28 flex-1 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-28 flex-1 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
                <div class="h-28 flex-1 rounded-lg bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
            </div>
        </div>
    </v-reports-dashboard>

    @pushOnce('scripts')
        <script type="text/x-template" id="v-reports-dashboard-template">
            <div class="flex flex-col gap-5">
                <!-- Header & Filters Bar -->
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3">
                        <span class="icon-activity text-3xl text-blue-600 dark:text-blue-400"></span>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                                Relatórios & Inteligência de Vendas
                            </h1>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                Período: <strong class="text-blue-600 dark:text-blue-400">@{{ filters.period_label }}</strong> (@{{ formatDate(filters.start_date) }} até @{{ formatDate(filters.end_date) }})
                            </p>
                        </div>
                    </div>

                    <!-- Filter Bar Form -->
                    <form :action="filterUrl" method="GET" class="flex flex-wrap items-center gap-2.5">
                        <!-- Period Preset -->
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Período:</label>
                            <select name="period" v-model="selectedPeriod" class="rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-700 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                <option value="today">Hoje</option>
                                <option value="yesterday">Ontem</option>
                                <option value="last_7_days">Últimos 7 dias</option>
                                <option value="last_30_days">Últimos 30 dias</option>
                                <option value="this_month">Este Mês</option>
                                <option value="last_month">Mês Passado</option>
                                <option value="this_year">Este Ano</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </div>

                        <!-- Custom Dates Input -->
                        <div v-if="selectedPeriod === 'custom'" class="flex items-center gap-1.5">
                            <input type="date" name="start_date" :value="filters.start_date" class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                            <span class="text-xs text-gray-400">até</span>
                            <input type="date" name="end_date" :value="filters.end_date" class="rounded-md border border-gray-300 bg-white px-2 py-1 text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" />
                        </div>

                        <!-- Pipeline Filter -->
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Funil:</label>
                            <select name="pipeline_id" :value="filters.pipeline_id || ''" class="rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-700 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                <option value="">Todos os Funis</option>
                                <option v-for="p in pipelines" :key="p.id" :value="p.id">@{{ p.name }}</option>
                            </select>
                        </div>

                        <!-- Sales Rep Filter -->
                        <div class="flex items-center gap-1.5">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Vendedor:</label>
                            <select name="user_id" :value="filters.user_id || ''" class="rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs text-gray-700 focus:border-blue-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                <option value="">Toda a Equipe</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">@{{ u.name }}</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <button type="submit" class="rounded-md bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                                Filtrar
                            </button>
                            <a :href="exportUrl" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                <span class="icon-export text-sm"></span> Exportar CSV
                            </a>
                        </div>
                    </form>
                </div>

                <!-- 5 KPI Cards Row -->
                <div style="display: flex; gap: 16px; flex-wrap: wrap; width: 100%;">
                    <!-- 1. Receita Total Ganha -->
                    <div style="flex: 1 1 180px; min-width: 180px;" class="flex flex-col justify-between rounded-lg border border-gray-200 border-l-4 border-l-green-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Receita Total Ganha</span>
                            <span class="icon-stats-up text-lg text-green-600 dark:text-green-400"></span>
                        </div>
                        <div class="mt-2 text-xl font-bold text-green-600 dark:text-green-400 truncate">
                            @{{ formatCurrency(metrics.total_won_revenue) }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <strong class="text-gray-900 dark:text-white">@{{ metrics.won_leads }}</strong> negócios fechados
                        </div>
                    </div>

                    <!-- 2. Ticket Médio -->
                    <div style="flex: 1 1 180px; min-width: 180px;" class="flex flex-col justify-between rounded-lg border border-gray-200 border-l-4 border-l-blue-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Ticket Médio</span>
                            <span class="icon-leads text-lg text-blue-600 dark:text-blue-400"></span>
                        </div>
                        <div class="mt-2 text-xl font-bold text-gray-900 dark:text-white truncate">
                            @{{ formatCurrency(metrics.average_ticket) }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Por venda concluída
                        </div>
                    </div>

                    <!-- 3. Taxa de Conversão -->
                    <div style="flex: 1 1 180px; min-width: 180px;" class="flex flex-col justify-between rounded-lg border border-gray-200 border-l-4 border-l-purple-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Taxa de Conversão</span>
                            <span class="icon-tick text-lg text-purple-600 dark:text-purple-400"></span>
                        </div>
                        <div class="mt-2 text-xl font-bold text-purple-600 dark:text-purple-400 truncate">
                            @{{ Number(metrics.win_rate).toFixed(1) }}%
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <span class="text-green-600 font-semibold">@{{ metrics.won_leads }} ganhos</span> / <span class="text-red-500 font-semibold">@{{ metrics.lost_leads }} perdidos</span>
                        </div>
                    </div>

                    <!-- 4. Pipeline em Aberto -->
                    <div style="flex: 1 1 180px; min-width: 180px;" class="flex flex-col justify-between rounded-lg border border-gray-200 border-l-4 border-l-amber-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Pipeline em Aberto</span>
                            <span class="icon-kanban text-lg text-amber-600 dark:text-amber-400"></span>
                        </div>
                        <div class="mt-2 text-xl font-bold text-amber-600 dark:text-amber-400 truncate">
                            @{{ formatCurrency(metrics.open_pipeline_value) }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            @{{ metrics.open_leads }} negócios em andamento
                        </div>
                    </div>

                    <!-- 5. Ciclo Médio de Venda -->
                    <div style="flex: 1 1 180px; min-width: 180px;" class="flex flex-col justify-between rounded-lg border border-gray-200 border-l-4 border-l-indigo-500 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Ciclo Médio de Venda</span>
                            <span class="icon-activity text-lg text-indigo-600 dark:text-indigo-400"></span>
                        </div>
                        <div class="mt-2 text-xl font-bold text-gray-900 dark:text-white truncate">
                            @{{ metrics.avg_sales_cycle_days }} dias
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Tempo médio até o fechamento
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation with Native Krayin Icons -->
                <div class="border-b border-gray-200 dark:border-gray-800 mt-1">
                    <div class="flex gap-2 overflow-x-auto">
                        <button
                            type="button"
                            @click="activeTab = 'overview'"
                            class="px-4 py-2.5 text-xs font-semibold border-b-2 transition flex items-center gap-2"
                            :class="activeTab === 'overview' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                        >
                            <span class="icon-activity text-sm"></span> Visão Geral
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'revenue'"
                            class="px-4 py-2.5 text-xs font-semibold border-b-2 transition flex items-center gap-2"
                            :class="activeTab === 'revenue' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                        >
                            <span class="icon-stats-up text-sm"></span> Vendas & Receita
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'funnel'"
                            class="px-4 py-2.5 text-xs font-semibold border-b-2 transition flex items-center gap-2"
                            :class="activeTab === 'funnel' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                        >
                            <span class="icon-kanban text-sm"></span> Funil & Conversão
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'tags'"
                            class="px-4 py-2.5 text-xs font-semibold border-b-2 transition flex items-center gap-2"
                            :class="activeTab === 'tags' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                        >
                            <span class="icon-settings-tag text-sm"></span> Desempenho por Tags
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'team'"
                            class="px-4 py-2.5 text-xs font-semibold border-b-2 transition flex items-center gap-2"
                            :class="activeTab === 'team' ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400'"
                        >
                            <span class="icon-settings-user text-sm"></span> Equipe & Produtividade
                        </button>
                    </div>
                </div>

                <!-- Tab 1: Visão Geral -->
                <div v-show="activeTab === 'overview'" class="flex flex-col gap-5">
                    <!-- Timeline Chart Box -->
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="mb-4">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">
                                Evolução de Receita & Oportunidades no Período
                            </h2>
                            <p class="text-xs text-gray-500">Acompanhamento diário de negócios concluídos e faturamento</p>
                        </div>
                        <div id="chart-timeline" class="w-full min-h-[330px]"></div>
                    </div>

                    <!-- 2 Columns: Funil & Ranking -->
                    <div style="display: flex; gap: 20px; flex-wrap: wrap; width: 100%;">
                        <!-- Funnel Summary -->
                        <div style="flex: 1 1 400px;" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">
                                Funil de Vendas (@{{ funnel.pipeline_name }})
                            </h2>
                            <div class="flex flex-col gap-3">
                                <div v-for="stg in activeFunnelStages" :key="stg.id" class="flex flex-col gap-1">
                                    <div class="flex justify-between text-xs">
                                        <span class="font-semibold text-gray-700 dark:text-gray-300">@{{ stg.name }}</span>
                                        <span class="text-gray-500">
                                            <strong>@{{ stg.count }}</strong> leads (@{{ stg.percentage }}%)
                                            <span v-if="stg.value > 0" class="text-green-600 font-bold ml-1">
                                                - @{{ formatCurrency(stg.value) }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="h-2.5 w-full bg-gray-100 rounded-full overflow-hidden dark:bg-gray-800">
                                        <div
                                            class="h-full rounded-full transition-all duration-500"
                                            :class="stg.code === 'won' ? 'bg-green-500' : (stg.code === 'lost' || stg.code.includes('perdido') ? 'bg-red-500' : 'bg-blue-500')"
                                            :style="{ width: Math.max(stg.percentage, (stg.count > 0 ? 8 : 2)) + '%' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Team Ranking -->
                        <div style="flex: 1 1 400px;" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">
                                Desempenho da Equipe
                            </h2>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 text-gray-500 uppercase text-[11px]">
                                            <th class="p-2.5">Vendedor</th>
                                            <th class="p-2.5 text-center">Ganhos</th>
                                            <th class="p-2.5 text-center">Conversão</th>
                                            <th class="p-2.5 text-right">Faturamento</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        <tr v-for="(rep, idx) in team.slice(0, 5)" :key="rep.user_id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <td class="p-2.5 font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                                <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 dark:bg-blue-950 dark:text-blue-400 font-bold text-[10px] inline-flex items-center justify-center">
                                                    #@{{ idx + 1 }}
                                                </span>
                                                @{{ rep.name }}
                                            </td>
                                            <td class="p-2.5 text-center font-bold text-green-600">@{{ rep.won }}</td>
                                            <td class="p-2.5 text-center font-medium">@{{ Number(rep.win_rate).toFixed(1) }}%</td>
                                            <td class="p-2.5 text-right font-bold text-gray-900 dark:text-white">@{{ formatCurrency(rep.revenue) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Vendas & Receita -->
                <div v-show="activeTab === 'revenue'" class="flex flex-col gap-5">
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-2">
                            Faturamento Diário Acumulado (R$)
                        </h2>
                        <div id="chart-revenue-detailed" class="w-full min-h-[350px]"></div>
                    </div>
                </div>

                <!-- Tab 3: Funil & Conversão (INVERTED FUNNEL CARDS WITH LOST BEFORE WON) -->
                <div v-show="activeTab === 'funnel'" class="flex flex-col gap-5">
                    
                    <!-- Top Container: Funnel Graphic Card (Left) + Health Panels (Right) -->
                    <div style="display: flex; gap: 20px; flex-wrap: wrap; width: 100%;">
                        
                        <!-- Left: Funnel Tapering Stages Card -->
                        <div style="flex: 1 1 500px;" class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between mb-5">
                                <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <span class="icon-kanban text-blue-600"></span>
                                    Etapas do Funil de Vendas (@{{ funnel.pipeline_name }})
                                </h2>
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                    @{{ activeFunnelStages.length }} Estágios
                                </span>
                            </div>

                            <!-- Inverted Tapering Funnel Steps Centered -->
                            <div class="flex flex-col items-center gap-3 w-full py-2">
                                <template v-for="(stg, index) in activeFunnelStages" :key="stg.id">
                                    
                                    <!-- Inverted Funnel Step Container -->
                                    <div
                                        class="flex items-center justify-between px-4 py-3 rounded-lg border transition-all duration-200 shadow-xs hover:shadow-sm"
                                        :style="getFunnelStepStyle(index, activeFunnelStages.length, stg.code)"
                                    >
                                        <!-- Left: Stage Name & Step Number -->
                                        <div class="flex items-center gap-2.5">
                                            <span
                                                class="w-6 h-6 rounded-full text-xs font-bold inline-flex items-center justify-center shadow-xs"
                                                :class="stg.code === 'won' ? 'bg-green-600 text-white' : (stg.code === 'lost' || stg.code.includes('perdido') ? 'bg-red-500 text-white' : 'bg-blue-600 text-white')"
                                            >
                                                @{{ index + 1 }}
                                            </span>
                                            <span class="font-bold text-sm text-gray-900 dark:text-white">
                                                @{{ stg.name }}
                                            </span>
                                        </div>

                                        <!-- Right: Count, Percentage & Value -->
                                        <div class="flex items-center gap-3">
                                            <span class="font-semibold text-xs text-gray-700 dark:text-gray-300">
                                                <strong class="text-gray-900 dark:text-white">@{{ stg.count }}</strong> leads (@{{ stg.percentage }}%)
                                            </span>
                                            <span v-if="stg.value > 0" class="font-bold text-green-600 text-xs bg-white dark:bg-gray-900 px-2 py-0.5 rounded border border-green-200 dark:border-green-800">
                                                @{{ formatCurrency(stg.value) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Connecting Funnel Arrow between stages -->
                                    <div v-if="index < activeFunnelStages.length - 1" class="text-gray-300 dark:text-gray-600 -my-1">
                                        <span class="icon-down-arrow text-xs"></span>
                                    </div>

                                </template>
                            </div>
                        </div>

                        <!-- Right: Conversion Health Cards -->
                        <div style="flex: 1 1 320px;" class="flex flex-col gap-4">
                            <!-- Conversion Rate Card -->
                            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Taxa de Conversão</span>
                                <div class="mt-2 text-3xl font-extrabold text-purple-600 dark:text-purple-400">
                                    @{{ Number(metrics.win_rate).toFixed(1) }}%
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    <strong class="text-green-600">@{{ metrics.won_leads }} negócios ganhos</strong> de @{{ metrics.won_leads + metrics.lost_leads }} fechados
                                </div>
                            </div>

                            <!-- Financial Outcomes Card -->
                            <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Balanço do Funil</span>
                                
                                <div class="mt-4 flex flex-col gap-3">
                                    <div class="flex justify-between items-center text-xs pb-2 border-b border-gray-100 dark:border-gray-800">
                                        <span class="text-gray-600 dark:text-gray-300 font-medium">Receita Fechada</span>
                                        <span class="font-bold text-green-600 text-sm">@{{ formatCurrency(metrics.total_won_revenue) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs pb-2 border-b border-gray-100 dark:border-gray-800">
                                        <span class="text-gray-600 dark:text-gray-300 font-medium">Pipeline em Aberto</span>
                                        <span class="font-bold text-amber-600 text-sm">@{{ formatCurrency(metrics.open_pipeline_value) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-xs">
                                        <span class="text-gray-600 dark:text-gray-300 font-medium">Ticket Médio</span>
                                        <span class="font-bold text-blue-600 text-sm">@{{ formatCurrency(metrics.average_ticket) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Bottom: Detailed Stages Breakdown Table -->
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">
                            Detalhamento por Estágio
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 text-gray-500 uppercase text-[11px]">
                                        <th class="p-3">Etapa / Estágio</th>
                                        <th class="p-3">Código</th>
                                        <th class="p-3 text-center">Quantidade</th>
                                        <th class="p-3 text-center">% do Total</th>
                                        <th class="p-3 text-right">Valor Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr v-for="stg in funnel.stages" :key="stg.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="p-3 font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                            <span
                                                class="w-2.5 h-2.5 rounded-full"
                                                :class="stg.code === 'won' ? 'bg-green-500' : (stg.code === 'lost' || stg.code.includes('perdido') ? 'bg-red-500' : 'bg-blue-500')"
                                            ></span>
                                            @{{ stg.name }}
                                        </td>
                                        <td class="p-3"><code class="bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded text-[11px]">@{{ stg.code }}</code></td>
                                        <td class="p-3 text-center font-bold">@{{ stg.count }}</td>
                                        <td class="p-3 text-center font-medium text-gray-500">@{{ stg.percentage }}%</td>
                                        <td class="p-3 text-right font-bold" :class="stg.value > 0 ? 'text-green-600' : 'text-gray-500'">
                                            @{{ formatCurrency(stg.value) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Tab 4: Tags -->
                <div v-show="activeTab === 'tags'" class="flex flex-col gap-5">
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">
                            Performance e Conversão por Tag do Chatwoot
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 text-gray-500 uppercase text-[11px]">
                                        <th class="p-3">Tag / Canal</th>
                                        <th class="p-3 text-center">Total de Leads</th>
                                        <th class="p-3 text-center">Ganhos</th>
                                        <th class="p-3 text-center">Conversão</th>
                                        <th class="p-3 text-right">Faturamento Gerado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr v-for="tag in tags" :key="tag.id">
                                        <td class="p-3">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-950 dark:text-blue-300 dark:border-blue-800">
                                                <span class="icon-settings-tag text-xs"></span>
                                                @{{ tag.name }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-center font-bold">@{{ tag.leads_count }}</td>
                                        <td class="p-3 text-center font-bold text-green-600">@{{ tag.won_count }}</td>
                                        <td class="p-3 text-center font-semibold">@{{ Number(tag.win_rate).toFixed(1) }}%</td>
                                        <td class="p-3 text-right font-bold text-green-600">@{{ formatCurrency(tag.revenue) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Equipe -->
                <div v-show="activeTab === 'team'" class="flex flex-col gap-5">
                    <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">
                            Ranking Detalhado da Equipe Comercial
                        </h2>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 text-gray-500 uppercase text-[11px]">
                                        <th class="p-3">Posição & Vendedor</th>
                                        <th class="p-3 text-center">Total Atribuído</th>
                                        <th class="p-3 text-center">Ganhos</th>
                                        <th class="p-3 text-center">Perdidos</th>
                                        <th class="p-3 text-center">Conversão</th>
                                        <th class="p-3 text-right">Receita Fechada</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    <tr v-for="(rep, idx) in team" :key="rep.user_id">
                                        <td class="p-3 font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                            <span class="w-6 h-6 rounded-full bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 inline-flex items-center justify-center font-bold text-xs">
                                                #@{{ idx + 1 }}
                                            </span>
                                            @{{ rep.name }}
                                        </td>
                                        <td class="p-3 text-center font-medium">@{{ rep.total }}</td>
                                        <td class="p-3 text-center font-bold text-green-600">@{{ rep.won }}</td>
                                        <td class="p-3 text-center font-medium text-red-500">@{{ rep.lost }}</td>
                                        <td class="p-3 text-center font-bold text-purple-600">@{{ Number(rep.win_rate).toFixed(1) }}%</td>
                                        <td class="p-3 text-right font-bold text-green-600 text-sm">@{{ formatCurrency(rep.revenue) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Activity Counters -->
                    <div style="display: flex; gap: 16px; flex-wrap: wrap; width: 100%;">
                        <div style="flex: 1 1 180px;" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-500">Notas Adicionadas</span>
                                <span class="icon-note text-base text-gray-400"></span>
                            </div>
                            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">@{{ activities.notes }}</div>
                        </div>
                        <div style="flex: 1 1 180px;" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-500">Chamadas Realizadas</span>
                                <span class="icon-call text-base text-gray-400"></span>
                            </div>
                            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">@{{ activities.calls }}</div>
                        </div>
                        <div style="flex: 1 1 180px;" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-500">E-mails Enviados</span>
                                <span class="icon-mail text-base text-gray-400"></span>
                            </div>
                            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">@{{ activities.emails }}</div>
                        </div>
                        <div style="flex: 1 1 180px;" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-gray-500">Tarefas Concluídas</span>
                                <span class="icon-tick text-base text-gray-400"></span>
                            </div>
                            <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">@{{ activities.tasks }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-reports-dashboard', {
                template: '#v-reports-dashboard-template',

                props: {
                    initialMetrics: Object,
                    initialFilters: Object,
                    initialTimeline: Object,
                    initialFunnel: Object,
                    initialTags: Array,
                    initialTeam: Array,
                    initialActivities: Object,
                    pipelines: Array,
                    users: Array,
                    exportUrl: String,
                    filterUrl: String
                },

                data() {
                    return {
                        metrics: this.initialMetrics || {},
                        filters: this.initialFilters || {},
                        timeline: this.initialTimeline || {},
                        funnel: this.initialFunnel || {},
                        tags: this.initialTags || [],
                        team: this.initialTeam || [],
                        activities: this.initialActivities || {},
                        activeTab: 'overview',
                        selectedPeriod: (this.initialFilters && this.initialFilters.period) || 'this_month',
                        timelineChart: null,
                        revenueChart: null
                    };
                },

                computed: {
                    // Sequence: Open stages -> Lost stages -> Won stage (last)
                    activeFunnelStages() {
                        const rawStages = (this.funnel && this.funnel.stages) || [];
                        const openStages = [];
                        const lostStages = [];
                        const wonStages = [];

                        rawStages.forEach(s => {
                            const code = (s.code || '').toLowerCase();
                            const name = (s.name || '').toLowerCase();
                            if (code === 'won') {
                                wonStages.push(s);
                            } else if (code === 'lost' || code.includes('lost') || code.includes('perdido') || name.includes('perdido')) {
                                lostStages.push(s);
                            } else {
                                openStages.push(s);
                            }
                        });

                        return [...openStages, ...lostStages, ...wonStages];
                    }
                },

                watch: {
                    activeTab(newTab) {
                        this.$nextTick(() => {
                            if (newTab === 'overview' && !this.timelineChart) {
                                this.initTimelineChart();
                            } else if (newTab === 'revenue' && !this.revenueChart) {
                                this.initRevenueChart();
                            }
                        });
                    }
                },

                mounted() {
                    this.$nextTick(() => {
                        this.initTimelineChart();
                    });
                },

                methods: {
                    formatCurrency(val) {
                        if (!val) return 'R$ 0,00';
                        return 'R$ ' + Number(val).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    },

                    formatDate(dateStr) {
                        if (!dateStr) return '';
                        const parts = dateStr.split('-');
                        if (parts.length === 3) return parts[2] + '/' + parts[1] + '/' + parts[0];
                        return dateStr;
                    },

                    getFunnelStepStyle(index, totalStages, code) {
                        // Tapering width from 100% down to 55%
                        const minW = 55;
                        const widthPercent = totalStages > 1 
                            ? Math.round(100 - (index * ((100 - minW) / (totalStages - 1))))
                            : 100;

                        if (code === 'won') {
                            return {
                                width: widthPercent + '%',
                                background: 'rgba(220, 252, 231, 0.7)',
                                borderColor: '#86efac'
                            };
                        }

                        if (code === 'lost' || code.includes('lost') || code.includes('perdido')) {
                            return {
                                width: widthPercent + '%',
                                background: 'rgba(254, 242, 242, 0.75)',
                                borderColor: '#fca5a5'
                            };
                        }

                        const backgrounds = [
                            'rgba(238, 242, 255, 0.7)', // soft indigo
                            'rgba(239, 246, 255, 0.7)', // soft blue
                            'rgba(240, 253, 250, 0.7)', // soft cyan
                            'rgba(254, 249, 195, 0.7)'  // soft amber
                        ];

                        const borders = [
                            '#c7d2fe',
                            '#bfdbfe',
                            '#99f6e4',
                            '#fde047'
                        ];

                        return {
                            width: widthPercent + '%',
                            background: backgrounds[index % backgrounds.length],
                            borderColor: borders[index % borders.length]
                        };
                    },

                    initTimelineChart() {
                        const chartEl = document.querySelector("#chart-timeline");
                        if (!chartEl || typeof ApexCharts === 'undefined') return;

                        const isDark = document.documentElement.classList.contains('dark');
                        const textColor = isDark ? '#94a3b8' : '#64748b';
                        const gridColor = isDark ? '#334155' : '#f1f5f9';

                        const options = {
                            chart: {
                                type: 'area',
                                height: 330,
                                toolbar: { show: false },
                                fontFamily: 'Inter, sans-serif',
                                zoom: { enabled: false }
                            },
                            series: [
                                { name: 'Receita Ganha (R$)', data: this.timeline.revenue_won || [], type: 'area' },
                                { name: 'Total de Oportunidades', data: this.timeline.leads_count || [], type: 'column' }
                            ],
                            xaxis: {
                                categories: this.timeline.labels || [],
                                labels: { style: { colors: textColor, fontSize: '11px' } }
                            },
                            yaxis: [
                                {
                                    title: { text: 'Faturamento (R$)', style: { color: '#059669', fontSize: '12px', fontWeight: 600 } },
                                    labels: {
                                        style: { colors: textColor },
                                        formatter: (val) => 'R$ ' + Math.round(val).toLocaleString('pt-BR')
                                    }
                                },
                                {
                                    opposite: true,
                                    title: { text: 'Oportunidades', style: { color: '#2563eb', fontSize: '12px', fontWeight: 600 } },
                                    labels: {
                                        style: { colors: textColor },
                                        formatter: (val) => Math.round(val)
                                    }
                                }
                            ],
                            colors: ['#10b981', '#93c5fd'],
                            stroke: { curve: 'smooth', width: [3, 0] },
                            fill: {
                                type: ['gradient', 'solid'],
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.45,
                                    opacityTo: 0.05,
                                },
                                opacity: [1, 0.6]
                            },
                            plotOptions: {
                                bar: { columnWidth: '40%', borderRadius: 4 }
                            },
                            grid: { borderColor: gridColor, strokeDashArray: 3 },
                            tooltip: { theme: isDark ? 'dark' : 'light' }
                        };

                        this.timelineChart = new ApexCharts(chartEl, options);
                        this.timelineChart.render();
                    },

                    initRevenueChart() {
                        const chartEl = document.querySelector("#chart-revenue-detailed");
                        if (!chartEl || typeof ApexCharts === 'undefined') return;

                        const isDark = document.documentElement.classList.contains('dark');
                        const textColor = isDark ? '#94a3b8' : '#64748b';
                        const gridColor = isDark ? '#334155' : '#f1f5f9';

                        const options = {
                            chart: {
                                type: 'line',
                                height: 350,
                                toolbar: { show: false },
                                fontFamily: 'Inter, sans-serif'
                            },
                            series: [
                                { name: 'Receita Fechada (R$)', data: this.timeline.revenue_won || [] }
                            ],
                            xaxis: {
                                categories: this.timeline.labels || [],
                                labels: { style: { colors: textColor } }
                            },
                            yaxis: {
                                labels: {
                                    style: { colors: textColor },
                                    formatter: (val) => 'R$ ' + Math.round(val).toLocaleString('pt-BR')
                                }
                            },
                            colors: ['#059669'],
                            stroke: { curve: 'smooth', width: 3 },
                            markers: { size: 4, colors: ['#059669'], strokeWidth: 2, strokeColors: '#fff' },
                            grid: { borderColor: gridColor, strokeDashArray: 3 },
                            tooltip: { theme: isDark ? 'dark' : 'light' }
                        };

                        this.revenueChart = new ApexCharts(chartEl, options);
                        this.revenueChart.render();
                    }
                }
            });
        </script>
    @endPushOnce
</x-admin::layouts>
