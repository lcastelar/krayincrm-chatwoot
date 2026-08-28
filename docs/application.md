# Aplicação e implementações

<!-- specsfy:documentator:start -->
## Superfícies

Categorias: Serviços, Rotas e APIs, Páginas, Componentes, Testes e Outras fontes.

Relação: relaciona cada arquivo observado à sua superfície.

| Categoria | Arquivo | Símbolos |
| --- | --- | --- |
| Outras fontes | packages/Webkul/Chatwoot/src/Config/acl.php | — |
| Outras fontes | packages/Webkul/Chatwoot/src/Config/chatwoot.php | — |
| Outras fontes | packages/Webkul/Chatwoot/src/Config/menu.php | — |
| Outras fontes | packages/Webkul/Chatwoot/src/Console/Commands/SyncTagsCommand.php | SyncTagsCommand, handle |
| Outras fontes | packages/Webkul/Chatwoot/src/Database/Migrations/2026_08_27_000001_create_chatwoot_webhook_logs_table.php | extends, up, down |
| Outras fontes | packages/Webkul/Chatwoot/src/Http/Controllers/ChatwootAdminController.php | ChatwootAdminController, __construct, index, ping, syncTagsNow, clearLogs |
| Outras fontes | packages/Webkul/Chatwoot/src/Http/Controllers/ChatwootApiController.php | ChatwootApiController, __construct, authenticate, getPayload, searchPersons, getLeads, createLead, updateLead |
| Outras fontes | packages/Webkul/Chatwoot/src/Http/Controllers/ChatwootEmbedController.php | ChatwootEmbedController, __construct, index, search, storeLead, updateStage, storeActivity |
| Outras fontes | packages/Webkul/Chatwoot/src/Http/Controllers/ChatwootWebhookController.php | ChatwootWebhookController, __construct, handle, syncContact, syncPersonTags, deleteContact, syncTagCreated, syncTagUpdated |
| Outras fontes | packages/Webkul/Chatwoot/src/Observers/PersonObserver.php | PersonObserver, __construct, created, updated, deleted |
| Outras fontes | packages/Webkul/Chatwoot/src/Observers/TagObserver.php | TagObserver, __construct, created, updated, deleted |
| Outras fontes | packages/Webkul/Chatwoot/src/Providers/ChatwootServiceProvider.php | ChatwootServiceProvider, boot, register |
| Páginas | packages/Webkul/Chatwoot/src/Resources/views/admin/index.blade.php | — |
| Páginas | packages/Webkul/Chatwoot/src/Resources/views/embed.blade.php | showToast, searchContact, renderFoundState, renderDealsList, escapeHtml, changeLeadStage, openFullLeadView, returnToSummary |
| Páginas | packages/Webkul/Chatwoot/src/Resources/views/unauthorized.blade.php | — |
| Rotas e APIs | packages/Webkul/Chatwoot/src/Routes/routes.php | — |
| Serviços | packages/Webkul/Chatwoot/src/Services/ChatwootApiService.php | ChatwootApiService, __construct, client, ping, getLabels, createLabel, updateLabel, deleteLabel |
| Serviços | packages/Webkul/Chatwoot/src/Services/SyncContext.php | SyncContext, setSource, getSource, isFromChatwoot, isFromKrayin, executeWithoutLoop |
| Outras fontes | packages/Webkul/Reports/src/Config/acl.php | — |
| Outras fontes | packages/Webkul/Reports/src/Config/menu.php | — |
| Outras fontes | packages/Webkul/Reports/src/Http/Controllers/ReportController.php | ReportController, index, getData, exportCsv, getFilteredReportData, resolveDateRange, buildTimelineData, buildFunnelData |
| Outras fontes | packages/Webkul/Reports/src/Providers/ReportsServiceProvider.php | ReportsServiceProvider, boot, register |
| Páginas | packages/Webkul/Reports/src/Resources/views/index.blade.php | — |
| Rotas e APIs | packages/Webkul/Reports/src/Routes/routes.php | — |
<!-- specsfy:documentator:end -->
