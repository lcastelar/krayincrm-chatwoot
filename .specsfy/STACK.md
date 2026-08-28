# Stack do sistema

Documente tecnologias estruturais e a evidência executável que confirma cada
uma. Preserve decisões humanas nas seções livres deste arquivo.

## Inventário detectado

<!-- specsfy:stack:start -->
| Camada | Tecnologia | Evidência |
| --- | --- | --- |
| Linguagem | PHP 8.2 ou superior | `packages/Webkul/Chatwoot/composer.json` |
| Framework | Laravel / Illuminate Support 11 ou 12 | `packages/Webkul/Chatwoot/composer.json` |
| Arquitetura | Módulos Laravel com autoload PSR-4 e service providers | `packages/Webkul/Chatwoot/composer.json`, `packages/Webkul/Reports/composer.json` |
<!-- specsfy:stack:end -->

## Decisões e observações do projeto

Acrescente aqui escolhas, restrições e contexto que não podem ser inferidos dos
manifests.
