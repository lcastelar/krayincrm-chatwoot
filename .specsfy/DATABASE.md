# Banco de dados

Mapa de persistência do addon Laravel. Ele usa a conexão de banco configurada
pela instalação hospedeira do Krayin CRM; credenciais e valores de ambiente não
são registrados aqui.

## Fontes de dados

<!-- specsfy:database:start -->
| Fonte | Tecnologia/forma | Evidência |
| --- | --- | --- |
| Banco do Krayin CRM | Laravel Schema Builder; conexão definida pela aplicação hospedeira | `packages/Webkul/Chatwoot/src/Database/Migrations/2026_08_27_000001_create_chatwoot_webhook_logs_table.php` |

## Estruturas detectadas

| Estrutura | Tipo | Campos | Relações | Fonte |
| --- | --- | --- | --- | --- |
| `chatwoot_webhook_logs` | Tabela de auditoria | `id`, `event`, `source`, `status`, `response_code`, `summary`, `payload`, `error_message`, timestamps | Sem relações declaradas; índices em `event`, `status` e `created_at` | `packages/Webkul/Chatwoot/src/Database/Migrations/2026_08_27_000001_create_chatwoot_webhook_logs_table.php` |
<!-- specsfy:database:end -->

## Decisões, ownership e retenção

Registre finalidade, ownership, classificação, retenção, constraints e decisões
que não estejam explícitas nos schemas.
