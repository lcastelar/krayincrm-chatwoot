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

<!-- specsfy:conversation-data:start -->
## Informações a guardar confirmadas

| Informação | Para que serve | O que guardar | Formato sugerido | Ligações | Quem usa | Quando muda ou sai | Fontes |
| --- | --- | --- | --- | --- | --- | --- | --- |
| Nome exibido e identificador interno do cliente no evento de auditoria | Permitir que administradores localizem eventos pelo cliente e distingam pessoas com o mesmo nome. | Nome exibido e identificador interno do cliente no momento em que o evento ocorreu; nenhum dado real é registrado nesta documentação. | Texto curto pesquisável e identificador interno. | O nome e o identificador permanecem associados ao respectivo evento de auditoria. | Consulta restrita a administradores; não há edição manual no log. | Preservar o nome e o identificador históricos, mesmo após alteração no cadastro do cliente. | specs/backlog/0001-integracao-chatwoot-krayin-automatizada-e-administravel.md; specs/inbox/2026-08-28-105518-paginacao-de-auditoria-e-cabecalho-reduzido-no-iframe.md |
| Retenção dos eventos de auditoria | Limitar o volume do histórico de auditoria e definir sua disponibilidade para administradores. | Eventos de auditoria por até 90 dias. | Data e horário de retenção. | Cada evento permanece associado ao seu registro de auditoria durante o período de retenção. | Consulta restrita a administradores; remoção executada automaticamente pelo sistema. | Remover automaticamente eventos com mais de 90 dias. | specs/backlog/0001-integracao-chatwoot-krayin-automatizada-e-administravel.md |
<!-- specsfy:conversation-data:end -->
