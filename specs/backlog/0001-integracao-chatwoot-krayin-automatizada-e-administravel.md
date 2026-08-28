# Backlog: Integração Chatwoot-Krayin automatizada e administrável

| Metainformação | Valor |
| --- | --- |
| ID | BACKLOG-0001 |
| Status | Ready for specification |
| Produto | Addon Chatwoot para Krayin CRM |
| Épico | Integração administrável Chatwoot-Krayin |
| Funcionalidade | Espelhamento automático de contatos e tags, auditoria e iframe |
| Tipo | Épico |
| Prioridade | Alta |
| Milestones | |
| Criado em | 2026-08-28 |
| Spec promovida | Nenhuma |

## Ideia original

Consolidar a sincronização automática de contatos e tags, com Chatwoot como fonte de verdade, e tornar a integração administrável por meio de controles de acesso, autenticação do iframe e auditoria escalável.

## Problema percebido

A integração precisa de regras consistentes de sincronização, acesso administrativo, sessão no iframe e consulta escalável de eventos de auditoria.

## Pessoa afetada ou beneficiada

Administradores e usuários que operam o Chatwoot e o Krayin CRM.

## Resultado ou valor esperado

Integração automática, segura, white-label e observável, sem alterar o repositório do Krayin.

## Contexto

Origem: specs/inbox/2026-08-28-105155-sincronizacao-automatica-com-chatwoot-como-fonte-de-verdade.md e specs/inbox/2026-08-28-105518-paginacao-de-auditoria-e-cabecalho-reduzido-no-iframe.md. Código relacionado: módulo packages/Webkul/Chatwoot.

## Referências relacionadas

- Nenhuma referência relevante encontrada.

## Comportamento esperado

O Chatwoot é a fonte de verdade para contatos e tags. O addon espelha esses
dados automaticamente no Krayin, com uma sincronização completa inicial
confirmada por administrador e webhooks em tempo real. Áreas administrativas,
auditoria e iframe respeitam sessão, autorização, privacidade e operação
white-label.

## Regras de negócio

- Contatos e tags no Krayin são espelhos do Chatwoot; alterações locais nesses dados não são permitidas.
- Tags sincronizadas do Chatwoot não são associadas a leads; o espelhamento é exclusivo dos contatos.
- Relatórios e Chatwoot são exibidos somente para administradores; suas rotas e APIs bloqueiam acesso direto de usuários não administradores.
- O iframe usa a sessão padrão do Krayin; sem sessão válida, exibe o login dentro do iframe e retorna ao conteúdo após autenticar.
- No detalhe de lead aberto pelo iframe do Chatwoot, ocultar somente o cabeçalho superior do Krayin e preservar o conteúdo e as ações existentes.
- O Log de Auditoria inicia filtrado pelos últimos 30 dias e permite selecionar qualquer intervalo de datas.
- Eventos do Log de Auditoria ficam disponíveis por 90 dias; os mais antigos são removidos automaticamente.
- Falhas de comunicação com o Chatwoot são registradas no Log de Auditoria e notificadas aos administradores, sem retentativa automática.
- A exclusão de um contato no Chatwoot exclui o contato correspondente no Krayin, preservando leads e negócios existentes.
- As ações locais de criar, editar e excluir contatos e tags continuam visíveis no Krayin, mas não executam alterações e orientam a pessoa a usar o Chatwoot.
- A exclusão de uma tag no Chatwoot remove a tag correspondente do catálogo, dos contatos e de quaisquer leads que já a possuam no Krayin.
- Webhooks repetidos são reconhecidos, não repetem alterações e são registrados como ignorados.
- O repositório não contém referências específicas à empresa da pessoa responsável nem dados provenientes de seu banco de dados; conteúdo genérico ou de terceiros pode permanecer.
- Falhas de sincronização são notificadas para uma URL de webhook configurável, além do registro no Log de Auditoria.
- Se o webhook de alerta falhar, o sistema tenta reenviá-lo automaticamente por até três vezes.
- O Log de Auditoria exibe 25 eventos por página.
- Alertas externos de falhas incluem o payload completo relacionado ao evento.
- O webhook de alerta não adiciona autenticação; a URL configurada é tratada como segredo operacional.
- Todo webhook recebido do Chatwoot exige assinatura válida; eventos sem assinatura válida são rejeitados e auditados.
- Qualquer usuário autenticado do Krayin pode acessar o iframe no Chatwoot, respeitando as permissões já existentes para cada ação.
- A busca por cliente no Log de Auditoria encontra qualquer parte do nome, sem diferenciar maiúsculas e minúsculas.
- O Log de Auditoria ordena eventos inicialmente do mais recente para o mais antigo.
- A higienização white-label inclui a reescrita do histórico Git publicado para remover referências específicas antigas.
- Não há clones externos conhecidos; a reescrita do histórico Git pode ocorrer imediatamente quando for autorizada na implementação.
- Alterações de contatos e tags no Chatwoot são refletidas no Krayin em tempo real, pelo webhook assim que ele chegar.
- Eventos de sincronização são processados pela ordem de chegada, inclusive quando um evento mais antigo substituir um estado mais recente.
- Campos vazios recebidos do Chatwoot limpam os valores correspondentes no Krayin para manter o espelho exato.
- Quando um webhook referir um contato inexistente no Krayin, o sistema cria automaticamente o contato e aplica o estado recebido.
- Quando uma tag for renomeada no Chatwoot, o Krayin renomeia a tag correspondente e preserva suas associações existentes.
- A primeira configuração da integração executa automaticamente uma sincronização completa do Chatwoot para o Krayin.
- Antes da sincronização inicial, o administrador é avisado de que a operação pode excluir dados do Krayin para refletir o estado do Chatwoot.
- A sincronização inicial exige confirmação explícita de um administrador depois desse aviso.
- Se a sincronização inicial falhar parcialmente, as alterações já aplicadas são mantidas; a falha é auditada e comunicada ao administrador para nova execução posterior.
- Na sincronização inicial, contatos existentes apenas no Krayin são excluídos para que o estado local reflita o Chatwoot.
- Na sincronização inicial, tags existentes apenas no Krayin são excluídas com suas associações, inclusive associações de leads já existentes.
- O espelho de cada contato é identificado pelo ID interno imutável do Chatwoot, persistido no Krayin como referência de integração.
- O espelho de cada tag é identificado pelo ID interno imutável do Chatwoot, persistido no Krayin como referência de integração.
- O detalhe de auditoria mostra resumo do evento, cliente e identificador, tipo de alteração, resultado e mensagem de erro, sem valores completos dos campos alterados.
- O bloqueio de alterações locais de contatos e tags é aplicado à interface do Krayin; as APIs existentes permanecem disponíveis.
- Cada instalação do addon sincroniza uma única conta do Chatwoot, definida na configuração administrativa.
- Todos os contatos da conta configurada participam da sincronização, independentemente de conversas, inbox ou status.
- Cada contato espelha os campos padrão compatíveis e todos os atributos personalizados disponíveis no Chatwoot.
- Quando um atributo personalizado não tiver equivalente local, a integração cria automaticamente um campo compatível no Krayin e o mantém sincronizado.
- Quando um atributo personalizado for removido do Chatwoot, o campo criado automaticamente e seus valores são excluídos do Krayin.
- Quando um atributo personalizado mudar de tipo no Chatwoot, a integração tenta converter os valores; incompatibilidades são auditadas e notificadas aos administradores.
- Quando um atributo personalizado tiver nome e tipo compatíveis com um campo preexistente no Krayin, a integração reutiliza esse campo.
- Para atributos complexos sem tipo equivalente, listas são convertidas para texto separado por vírgulas e objetos não são sincronizados.
- O segredo de validação dos webhooks é configurado somente por administradores, armazenado criptografado e não é exibido após ser salvo.
- A URL de alerta administrativo é configurável somente por administradores, armazenada criptografada e exibida mascarada após salvar.
- Se a conta ou as credenciais do Chatwoot forem alteradas após a configuração inicial, a alteração vale apenas para webhooks futuros e não dispara nova sincronização completa automaticamente.
- Após falha, um administrador pode iniciar uma nova sincronização completa por botão próprio, sujeito ao mesmo aviso e confirmação explícita.
- A integração permanece ativa enquanto estiver configurada; não há modo de pausa temporária.
- Se cookies de terceiros impedirem a sessão no iframe, a aba explica a limitação e oferece abrir o Krayin em nova aba para autenticação.
- O Krayin só pode ser incorporado pela origem HTTPS do Chatwoot configurada no addon.
- Usuários não administradores que acessem URLs diretas de Relatórios ou Chatwoot são redirecionados ao painel inicial, sem dados administrativos.
- Webhooks válidos com evento ou estrutura não suportados não alteram dados, ficam registrados como falha e geram alerta aos administradores.
- A configuração inicial testa automaticamente conexão, credenciais e conta do Chatwoot; a confirmação da sincronização só é habilitada quando a validação for bem-sucedida.
- Ao excluir um contato espelhado, o Krayin remove o contato e seus vínculos com leads e negócios, preservando os próprios registros de lead e negócio.
- Webhooks válidos são processados por fila e devem refletir no Krayin em até um minuto na operação normal.
- A sincronização inicial deve suportar uma conta do Chatwoot com até 250 mil contatos.
- Para o volume-alvo, a sincronização inicial deve concluir em até quatro horas em condições normais.
- Durante a sincronização inicial, a tela administrativa mostra estado, progresso, total processado, criados, atualizados, excluídos, falhas e horário de início.
- Somente uma sincronização completa pode executar por vez; novas tentativas durante a execução mostram o progresso existente e não iniciam outro processo.
- Uma sincronização completa confirmada não pode ser cancelada; ela segue até concluir ou falhar.
- Alterações feitas pelas APIs existentes do Krayin não são interceptadas nem enviadas ao Chatwoot; atualização futura do Chatwoot pode sobrescrever o estado local.
- Webhooks recebidos durante uma sincronização completa são enfileirados e processados pela ordem de chegada após seu término.
- Se uma sincronização completa falhar, os webhooks acumulados durante ela permanecem bloqueados até nova sincronização completa administrativa.
- Depois que a nova sincronização completa terminar com sucesso, os webhooks previamente bloqueados são processados pela ordem de chegada.
- Depois de processar um webhook com sucesso, o addon remove o payload completo e conserva apenas o resumo de auditoria por 90 dias.
- Webhooks com falha ou assinatura inválida retêm apenas resumo técnico e identificadores necessários à auditoria, sem payload completo.
- A busca do Log de Auditoria localiza eventos por nome parcial ou identificador interno do cliente.
- A higienização white-label do histórico Git remove referências específicas e dados de banco, mas preserva a autoria histórica dos commits.
- Novos commits usam a identidade configurada localmente por cada colaborador.
- Ao abrir a aba KrayinCRM para um contato do Chatwoot ainda inexistente localmente, o addon pode criar imediatamente o contato espelhado, mas não cria lead.
- Leads são criados somente manualmente ou por automações que utilizem a API; não são criados pela sincronização nem pela aba KrayinCRM.
- A aba KrayinCRM preserva integralmente a tela existente de contato e negócios do Krayin, inclusive quando houver múltiplos negócios; não cria seletor de lead nem altera essa interface.
- No iframe, somente o cabeçalho global do Krayin é ocultado quando existir; toda a tela existente de contato e negócios permanece intacta.
- Quando um atributo removido do Chatwoot usava um campo preexistente reutilizado no Krayin, a sincronização desse atributo é encerrada, mas o campo e seus valores são preservados.
- Se um atributo reutilizar campo preexistente e mudar de tipo no Chatwoot, o Krayin preserva seu tipo local, tenta converter valores e alerta diante de incompatibilidade.
- Cada tag espelhada contém o nome e a cor recebidos do Chatwoot.
- A lista atual de tags recebida para um contato substitui integralmente as tags desse contato no Krayin.

## Critérios de aceitação

- Dado um webhook assinado válido do Chatwoot, quando ele criar, editar ou excluir
  contato ou tag, então o Krayin reflete a alteração em fila em até um minuto,
  sem associar tags sincronizadas a leads.
- Dado um webhook repetido, quando for recebido novamente, então nenhuma alteração
  adicional é aplicada e o evento fica auditado como ignorado.
- Dado um administrador com configuração validada, quando confirmar a sincronização
  completa após o aviso de risco, então o processo espelha toda a conta configurada,
  suporta até 250 mil contatos e mostra progresso detalhado.
- Dado um usuário não administrador, quando acessar menu ou URL de Relatórios ou
  Chatwoot, então não vê os menus e é redirecionado ao painel inicial sem dados da área.
- Dado um usuário autenticado no Krayin que abra o iframe pelo Chatwoot, quando a
  sessão for válida, então a tela existente do Krayin é preservada e só o cabeçalho
  global é ocultado; sem sessão, é mostrado o login padrão do Krayin.
- Dado um administrador, quando consultar o Log de Auditoria, então pode filtrar por
  intervalo de datas e pesquisar por nome parcial ou identificador interno, com 25
  eventos por página, mais recentes primeiro e retenção de 90 dias.

## Qualidades e operação

- Segurança: assinatura de webhook obrigatória; segredo e URL de alerta criptografados;
  iframe permitido apenas para a origem HTTPS configurada do Chatwoot.
- Privacidade: payloads completos não são retidos; logs mantêm apenas resumo e
  identificadores por 90 dias. A higienização white-label remove referências e dados
  específicos, inclusive no histórico Git publicado.
- Desempenho e volume: webhooks em até um minuto; sincronização inicial de até 250 mil
  contatos em até quatro horas em condições normais; apenas uma execução completa ativa.
- Auditoria e observabilidade: falhas e eventos não suportados são auditados e alertam
  administradores por webhook configurável, com até três tentativas de entrega.

## Dependências

- Nenhuma registrada.

## Situações de erro

- Assinatura inválida, evento não suportado e falhas de sincronização não alteram dados
  indevidamente, ficam auditados e geram alerta quando aplicável.
- Falha parcial de sincronização completa preserva alterações já feitas, bloqueia os
  webhooks acumulados e requer nova execução administrativa confirmada.
- Cookies de terceiros bloqueados no iframe exibem orientação e alternativa de abertura
  do Krayin em nova aba.

## Escopo

- Dentro: sincronização Chatwoot → Krayin de contatos, tags e atributos personalizados;
  autorização administrativa; auditoria; configuração; iframe; higienização white-label
  e atualização contínua do README.
- Fora: alterar o código-base do Krayin; criar leads automaticamente; sincronizar dados
  do Krayin para o Chatwoot; pausar a integração; alterar a tela existente de contato e
  negócios no iframe.

## Dúvidas, decisões e riscos

- **Decisão confirmada:** contatos e tags devem ser alterados somente no Chatwoot; o Krayin apenas reflete esse estado.
- **Decisão confirmada:** não propagar tags sincronizadas para `lead_tags`.
- **Decisão confirmada:** ocultar as áreas administrativas para não administradores e aplicar autorização no servidor às rotas e APIs correspondentes.
- **Decisão confirmada:** autenticação ausente no iframe é resolvida pela tela padrão de login do Krayin no próprio iframe, com retorno ao conteúdo solicitado.
- **Decisão confirmada:** a visualização embutida de lead remove apenas o cabeçalho superior; não cria uma tela alternativa nem oculta as ações do detalhe.
- **Decisão confirmada:** a consulta inicial do Log de Auditoria cobre os últimos 30 dias, com intervalo de datas livre.
- **Decisão confirmada:** a retenção dos eventos de auditoria é de 90 dias, com remoção automática após esse período.
- **Decisão confirmada:** uma falha de sincronização não é reenfileirada automaticamente; fica auditada e requer acompanhamento administrativo.
- **Decisão confirmada:** a exclusão de contato segue o Chatwoot como fonte de verdade; leads e negócios vinculados são preservados.
- **Decisão confirmada:** o Krayin comunica a autoridade do Chatwoot ao bloquear tentativas de alteração local, sem ocultar as ações existentes.
- **Decisão confirmada:** a exclusão de uma tag segue o Chatwoot em todas as associações existentes, inclusive `lead_tags`.
- **Decisão confirmada:** o processamento de webhooks é idempotente; repetições não produzem efeito adicional e permanecem auditáveis.
- **Decisão confirmada:** a higienização white-label é limitada à remoção de referências à empresa da pessoa responsável e de dados do seu banco; não exige remover todo conteúdo não genérico.
- **Decisão confirmada:** a notificação administrativa de falhas é entregue a uma URL de webhook configurável.
- **Decisão confirmada:** alertas que falharem recebem até três tentativas automáticas de reenvio.
- **Decisão confirmada:** a paginação padrão do Log de Auditoria usa 25 eventos por página.
- **Decisão confirmada:** o webhook de alerta recebe o payload completo; a proteção da URL configurada é requisito de segurança material.
- **Decisão confirmada:** o destino de alertas não recebe assinatura ou token; a confidencialidade depende do sigilo da URL.
- **Decisão confirmada:** a assinatura de webhooks recebidos do Chatwoot é obrigatória para qualquer sincronização.
- **Decisão confirmada:** o iframe não exige vínculo individual prévio com o Chatwoot nem perfil administrativo; aplica as permissões existentes do usuário autenticado no Krayin.
- **Decisão confirmada:** a busca nominal do Log de Auditoria é parcial e não diferencia maiúsculas de minúsculas.
- **Decisão confirmada:** a ordenação padrão do Log de Auditoria é decrescente por data e hora.
- **Decisão confirmada:** a remoção de referências específicas alcança o histórico Git já publicado; a execução exige planejamento e autorização operacional próprios.
- **Decisão confirmada:** não é necessário manter compatibilidade com clones antigos, pois não há usuários externos conhecidos; a reescrita poderá ser feita imediatamente na etapa autorizada.
- **Decisão confirmada:** a sincronização do Chatwoot para o Krayin é orientada a webhooks em tempo real, não por execução agendada ou manual.
- **Decisão confirmada:** não há detecção de evento atrasado; a última entrega processada define o estado espelhado.
- **Decisão confirmada:** o estado do Chatwoot prevalece inclusive para campos vazios; valores locais correspondentes são removidos.
- **Decisão confirmada:** a ausência de contato local não bloqueia a sincronização; o contato é criado a partir do evento do Chatwoot.
- **Decisão confirmada:** uma alteração de nome de tag atualiza a tag correspondente em vez de duplicar ou perder associações.
- **Decisão confirmada:** a sincronização inicial deve alertar administradores sobre possível exclusão de dados locais para alinhar o Krayin ao Chatwoot.
- **Decisão confirmada:** a sincronização inicial só inicia depois de uma confirmação explícita de administrador, após o alerta de possível exclusão local.
- **Decisão confirmada:** uma falha parcial na sincronização inicial não faz rollback; preserva as alterações aplicadas, registra a falha e alerta o administrador.
- **Decisão confirmada:** contatos sem correspondente no Chatwoot são excluídos do Krayin durante a sincronização inicial.
- **Decisão confirmada:** tags sem correspondente no Chatwoot são excluídas do Krayin durante a sincronização inicial, inclusive de `lead_tags`.
- **Decisão confirmada:** o ID interno imutável do contato no Chatwoot é a chave de vinculação com o contato espelhado no Krayin.
- **Decisão confirmada:** o ID interno imutável da tag no Chatwoot é a chave de vinculação com a tag espelhada no Krayin.
- **Decisão confirmada:** o detalhe da auditoria não expõe valores anteriores ou novos completos dos campos sincronizados.
- **Decisão confirmada:** apenas a interface impede alterações locais; APIs atuais de contatos e tags não serão bloqueadas por esta integração.
- **Decisão confirmada:** a integração opera sobre uma única conta do Chatwoot por instalação.
- **Decisão confirmada:** o escopo de contatos é toda a conta configurada do Chatwoot.
- **Decisão confirmada:** atributos personalizados do Chatwoot fazem parte do espelhamento de contatos.
- **Decisão confirmada:** atributos personalizados sem campo correspondente criam automaticamente campos personalizados compatíveis no Krayin.
- **Decisão confirmada:** a remoção de atributo personalizado no Chatwoot exclui o campo criado automaticamente e seus valores no Krayin.
- **Decisão confirmada:** mudanças de tipo em atributos personalizados fazem conversão automática quando possível; falhas de conversão não são silenciosas.
- **Decisão confirmada:** campos personalizados preexistentes são reutilizados automaticamente quando nome e tipo correspondem ao atributo do Chatwoot.
- **Decisão confirmada:** listas de atributos personalizados tornam-se texto separado por vírgulas; objetos incompatíveis são ignorados.
- **Decisão confirmada:** o segredo de webhook é administrado somente por administradores, fica criptografado em repouso e não pode ser revelado pela interface.
- **Decisão confirmada:** a URL secreta de alerta é protegida por criptografia em repouso e máscara na interface administrativa.
- **Decisão confirmada:** alterações posteriores da configuração do Chatwoot não executam automaticamente uma nova sincronização inicial.
- **Decisão confirmada:** a recuperação de sincronização inicial é uma ação administrativa explícita, nunca uma retentativa automática.
- **Decisão confirmada:** não há controle de pausa da integração configurada.
- **Decisão confirmada:** bloqueio de cookies de terceiros recebe orientação e alternativa de abertura do Krayin em nova aba.
- **Decisão confirmada:** a política de incorporação permite exclusivamente a origem HTTPS configurada do Chatwoot.
- **Decisão confirmada:** acesso direto não autorizado a Relatórios ou Chatwoot redireciona para o painel inicial, em vez de retornar uma página 403.
- **Decisão confirmada:** evento válido, porém não suportado, é falha operacional auditada e notificada; não é ignorado silenciosamente nem reenviado ao Chatwoot.
- **Decisão confirmada:** a confirmação da sincronização inicial depende de validação automática bem-sucedida da conexão e da conta configurada.
- **Decisão confirmada:** exclusões de contato desvinculam leads e negócios, mas não excluem esses registros dependentes.
- **Decisão confirmada:** a sincronização em tempo real é assíncrona por fila, com objetivo operacional de até um minuto por webhook válido.
- **Decisão confirmada:** o volume-alvo para a sincronização inicial é de até 250 mil contatos.
- **Decisão confirmada:** o objetivo de duração da sincronização inicial é de até quatro horas para 250 mil contatos em condições normais.
- **Decisão confirmada:** o acompanhamento administrativo da sincronização inicial é detalhado e atualizado enquanto a execução estiver em curso.
- **Decisão confirmada:** sincronizações completas concorrentes são bloqueadas.
- **Decisão confirmada:** sincronizações completas não são canceláveis depois da confirmação.
- **Decisão confirmada:** APIs existentes podem alterar dados espelhados sem auditoria adicional do addon; o próximo evento aplicável do Chatwoot pode substituir o estado local.
- **Decisão confirmada:** webhooks concorrentes aguardam o fim da sincronização completa, preservando a ordem de chegada.
- **Decisão confirmada:** falha de sincronização completa mantém bloqueados os webhooks acumulados até uma nova execução completa confirmada por administrador.
- **Decisão confirmada:** após recuperação bem-sucedida, webhooks bloqueados são processados em ordem e não descartados.
- **Decisão confirmada:** payloads completos de webhooks processados com sucesso não são retidos; apenas o resumo de auditoria permanece por 90 dias.
- **Decisão confirmada:** falhas e rejeições de webhooks também não retêm payload completo.
- **Decisão confirmada:** a pesquisa de auditoria cobre nome parcial e identificador interno do cliente.
- **Decisão confirmada:** identidades de autor e committer existentes não entram na reescrita white-label do histórico Git.
- **Decisão confirmada:** a autoria de commits futuros segue a identidade Git de cada colaborador; não há identidade genérica obrigatória.
- **Decisão confirmada:** a abertura da aba KrayinCRM pode criar sob demanda apenas o contato que ainda não existir no Krayin.
- **Decisão confirmada:** o addon não cria leads; sua criação continua manual ou por automação via API.
- **Decisão confirmada:** para contatos com múltiplos leads ou negócios, a aba mantém a tela existente do Krayin sem mudança de layout ou novo fluxo de seleção.
- **Decisão confirmada:** a remoção do cabeçalho global no iframe permanece; não alcança a tela existente de contato e negócios.
- **Decisão confirmada:** a exclusão estrutural de campo por remoção de atributo só alcança campos criados pela integração, nunca campos preexistentes reutilizados.
- **Decisão confirmada:** mudança de tipo não altera a estrutura de campos preexistentes reutilizados.
- **Decisão confirmada:** as propriedades sincronizadas de tag são nome e cor.
- **Decisão confirmada:** associações de tags do contato obedecem ao estado completo recebido do Chatwoot, sem preservação de tags locais ausentes.

## Pronto para desenvolvimento

- [x] O problema e a pessoa beneficiada estão claros.
- [x] O evento inicial e o resultado esperado estão claros.
- [x] Permissões, regras e exceções relevantes estão claras.
- [x] O resultado pode ser verificado objetivamente.
- [x] Segurança, privacidade e desempenho foram avaliados conforme o risco.
- [x] Fora de escopo, dependências e decisões pendentes estão registrados.

## Próximo passo

Promover para `$specsfy-03-specify` e criar a especificação normativa.
