# Inbox: Paginação de auditoria e cabeçalho reduzido no iframe

| Metadado | Valor |
| --- | --- |
| Status | Capturada |
| Capturada em | 2026-08-28T13:55:18Z |
| Slug | paginacao-de-auditoria-e-cabecalho-reduzido-no-iframe |
| Origem | Input do usuário |
| Processamento | Análise inicial sem perguntas |
| Sessão de descoberta | Captura avulsa. |
| Turno da conversa | Não se aplica. |
| Integridade do original | SHA-256 `88a84c7700d3b5c95ed85ba43c8d3070685b84930bdebe4ca534d709e9c3148c` |
| Backlog derivado | Nenhum |
| Spec derivada | Nenhuma |

## Texto original

Na aba de integracao do chatwoot, no Log de Auditoria de Eventos e Webhooks, quero que ele tenha paginacao, senao se torna uma lista infinita. Tambem quero que tenha como pesquisar por data e nome do cliente.
Enviei uma foto, gostaria de saber se no iframe do Krayin no chatwoot, seria possivel remover o header do Krayin ao ver um lead por la. Nao quero alterar o código do Krayin, somente quero fazzer se for possivel via addon

## Contexto consultado

Nenhuma fonte contextual consultada.

## Resumo processado

**Inferência:** Adicionar paginação e filtros ao log de auditoria do Chatwoot e avaliar a remoção do cabeçalho do Krayin no iframe por meio do addon.

## Análise inicial

### Problema ou oportunidade

**Declaração ou inferência identificada:** O log de auditoria pode crescer indefinidamente e o iframe exibe um cabeçalho que reduz a área útil ao visualizar um lead.

### Pessoas afetadas ou beneficiadas

**Declaração ou inferência identificada:** Administradores que consultam o log de auditoria e usuários que visualizam leads no iframe do Krayin no Chatwoot.

### Resultado ou valor esperado

**Declaração ou inferência identificada:** Navegação escalável dos eventos auditados e uma visualização de lead mais focada dentro do Chatwoot, sem modificar o código do Krayin.

### Sinais de escopo, regras ou solução

**Sinais extraídos, não decisões:** Paginação; filtro por data; pesquisa por nome do cliente; aba de integração do Chatwoot; log de auditoria de eventos e webhooks; iframe do Krayin no Chatwoot; não alterar código do Krayin; solução somente via addon.

### Informações que talvez precisem ser guardadas

**Sinais para conversar depois, não confirmação:** Não identificado no texto original.

### Riscos e dependências

**Análise preliminar:** Definir o relacionamento entre eventos auditados e o nome pesquisável do cliente; preservar desempenho e auditoria; limitações de CSS, iframe e origem cruzada; possível indisponibilidade de ocultar o cabeçalho somente pelo addon.

## Possíveis direções futuras

**Hipóteses para backlog ou spec, não requisitos:** Refinar paginação e filtros do log; investigar os pontos de extensão do addon para uma visualização de iframe sem cabeçalho; documentar a viabilidade técnica sem mudar o repositório de referência Krayin.

## Pontos a revisar no futuro

**A revisar:** Confirmar formato e fuso de data, comportamento de busca, tamanho de página, ordenação, permissões e a viabilidade da remoção do cabeçalho via addon.

## Rastreabilidade

- Formulação original preservada integralmente nesta captura.
- Análises não substituem decisões do usuário.
- Backlogs e specs derivados devem referenciar este arquivo.

## Próximo passo

Manter em `specs/inbox/` ou refinar com `$specsfy-02-backlog`.
