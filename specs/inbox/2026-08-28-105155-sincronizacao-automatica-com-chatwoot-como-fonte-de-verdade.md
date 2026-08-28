# Inbox: Sincronização automática com Chatwoot como fonte de verdade

| Metadado | Valor |
| --- | --- |
| Status | Capturada |
| Capturada em | 2026-08-28T13:51:55Z |
| Slug | sincronizacao-automatica-com-chatwoot-como-fonte-de-verdade |
| Origem | Input do usuário |
| Processamento | Análise inicial sem perguntas |
| Sessão de descoberta | Captura avulsa. |
| Turno da conversa | Não se aplica. |
| Integridade do original | SHA-256 `30bfe91a92b70460503376c428b92bb970b2f92934ac747611c270e69c89cf72` |
| Backlog derivado | Nenhum |
| Spec derivada | Nenhuma |

## Texto original

Quero que que toda a sincronização seja feita automaticamente. Tanto criacao, edicao, exclusao de contatos, quanto de tags no sistema, e que seja feito o mesmo para os 2 lados, mas utilizando o chatwoot como fonte de verdade.
Na pasta <workspace>, há os repositórios do Krayin e do Chatwoot para consulta estrutural.
Quero que a aba de relatórios e chatwoot sejam vistas somente por admins.
O iframe do Krayin no chatwoot tem que utilizar a autenticacao atual do Krayin no navegador onde o chatwoot foi aberto. Caso nao esteja logado, mostre a tela de login.
Tudo nesse repositório tem que ser impessoal, estou criando para minha empresa, mas quero deixar como opensource no github, entao NUNCA pode ter nenhuma informacao minha ou da minha empresa, logo, contatos, NADA. Tem que ser whitelabel para que qualquer pessoa possa utilizar.
Sempre que alterar qualquer coisa, altere o README.md, para que mostre na pagina inicial do github.
Como estamos programando em laravel, sempre utilize o $specsfy-specialist-laravel.

## Contexto consultado

Nenhuma fonte contextual consultada.

## Resumo processado

**Inferência:** Automatizar a sincronização bidirecional de contatos e tags, com Chatwoot como fonte de verdade, controles administrativos e requisitos de autenticação e white-label.

## Análise inicial

### Problema ou oportunidade

**Declaração ou inferência identificada:** A sincronização atual precisa cobrir criação, edição e exclusão de contatos e tags nos dois sistemas, com regras de acesso e autenticação explícitas.

### Pessoas afetadas ou beneficiadas

**Declaração ou inferência identificada:** Administradores e usuários dos sistemas Krayin e Chatwoot.

### Resultado ou valor esperado

**Declaração ou inferência identificada:** Sincronização automática e confiável, com interface administrativa restrita, experiência integrada no iframe e distribuição open source white-label.

### Sinais de escopo, regras ou solução

**Sinais extraídos, não decisões:** Chatwoot como fonte de verdade; operações de criar, editar e excluir contatos e tags; abas Relatórios e Chatwoot restritas a admins; iframe reutiliza autenticação do Krayin e exibe login quando ausente; README deve ser atualizado em toda alteração; usar especialista Laravel.

### Informações que talvez precisem ser guardadas

**Sinais para conversar depois, não confirmação:** Não identificado no texto original.

### Riscos e dependências

**Análise preliminar:** Conflitos na sincronização bidirecional; mapeamento de identidade e sessão entre sistemas; exclusão de dados; exposição acidental de dados ou identidade da empresa; compatibilidade das estruturas do Krayin e Chatwoot.

## Possíveis direções futuras

**Hipóteses para backlog ou spec, não requisitos:** Refinar regras de autoridade do Chatwoot, eventos de sincronização, controles de acesso, autenticação do iframe, estratégia white-label e atualização documental.

## Pontos a revisar no futuro

**A revisar:** Definir a resolução de conflitos e falhas; confirmar mecanismos de autenticação e sessão entre domínios; identificar telas e permissões exatas; estabelecer política de remoção e migração de dados; detalhar critérios de aceite e testes.

## Rastreabilidade

- Formulação original preservada integralmente nesta captura.
- Análises não substituem decisões do usuário.
- Backlogs e specs derivados devem referenciar este arquivo.

## Próximo passo

Manter em `specs/inbox/` ou refinar com `$specsfy-02-backlog`.
