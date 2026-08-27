# Krayin CRM - Chatwoot Integration & Advanced Analytics Add-ons

[![Build and Publish Docker Image](https://github.com/lcastelar/krayincrm-chatwoot/actions/workflows/docker-build.yml/badge.svg)](https://github.com/lcastelar/krayincrm-chatwoot/actions/workflows/docker-build.yml)
[![Docker Image](https://img.shields.io/badge/Docker-ghcr.io%2Flcastelar%2Fkrayincrm--chatwoot-blue?logo=docker)](https://github.com/lcastelar/krayincrm-chatwoot/pkgs/container/krayincrm-chatwoot)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)

Módulos de extensão (add-ons) para o **[Krayin CRM](https://krayincrm.com)** que adicionam integração nativa com o **[Chatwoot](https://www.chatwoot.com)** e um painel completo de **Relatórios & Inteligência de Vendas**.

---

## 📦 Módulos Inclusos

### 1. 💬 Chatwoot Integration (`Webkul\Chatwoot`)
- **Dashboard App Embutido**: Permite aos atendentes do Chatwoot visualizar dados do contato no CRM, histórico de compras, negócios em andamento, valores fechados e alterar etapas do funil diretamente da barra lateral do Chatwoot sem trocar de aba.
- **Sincronização Bidirecional via Webhook**: Sincroniza novos contatos, atualizações e tags/etiquetas entre o Chatwoot e o Krayin CRM em tempo real.

### 2. 📊 Relatórios & Inteligência de Vendas (`Webkul\Reports`)
- **Visão Geral & KPIs**: Receita total ganha, ticket médio, taxa de conversão global, pipeline em aberto e ciclo médio de venda.
- **Gráfico de Evolução Temporal**: Acompanhamento diário e mensal de faturamento e volume de oportunidades via ApexCharts.
- **Funil de Vendas Cônico**: Desenho progressivo do funil com contagem de leads, porcentagens de conversão por nível e destaque de valores.
- **Desempenho por Tags / Canais**: Análise detalhada de faturamento, volume de leads e taxa de conversão por tag de origem do Chatwoot.
- **Ranking & Produtividade da Equipe**: Desempenho individual de vendedores (negócios ganhos/perdidos, conversão e receita) + métricas de atividades (ligações, e-mails, notas e tarefas).

---

## 🚀 Como Usar (Deploy Rápido com Docker / Portainer)

A imagem pronta com todos os add-ons já configurados e otimizados é compilada automaticamente e está disponível no GitHub Container Registry:

```text
ghcr.io/lcastelar/krayincrm-chatwoot:latest
```

### Exemplo de `docker-compose.yml` / Stack do Portainer:

```yaml
version: '3.8'

services:
  krayin_app:
    image: ghcr.io/lcastelar/krayincrm-chatwoot:latest
    environment:
      APP_NAME: "Krayin CRM"
      APP_ENV: production
      APP_URL: https://crm.seu-dominio.com

      # Banco de dados MySQL / Percona
      DB_CONNECTION: mysql
      DB_HOST: krayin_db
      DB_PORT: 3306
      DB_DATABASE: krayincrm
      DB_USERNAME: root
      DB_PASSWORD: SUA_SENHA_DO_BANCO

      # Cache e Sessão com Redis
      REDIS_HOST: krayin_redis
      REDIS_PORT: 6379
      CACHE_STORE: redis
      SESSION_DRIVER: redis
      QUEUE_CONNECTION: redis

      # Integração com Chatwoot
      CHATWOOT_URL: https://chat.seu-dominio.com
      CHATWOOT_API_TOKEN: SEU_ACCESS_TOKEN_DO_CHATWOOT
      CHATWOOT_ACCOUNT_ID: 1
    deploy:
      replicas: 1
      restart_policy:
        condition: any
    networks:
      - traefik_public
      - internal

  krayin_db:
    image: percona/percona-server:8.0
    command: --default-authentication-plugin=mysql_native_password --sql-mode=""
    environment:
      MYSQL_ROOT_PASSWORD: SUA_SENHA_DO_BANCO
      MYSQL_DATABASE: krayincrm
    volumes:
      - krayin_db_data:/var/lib/mysql
    networks:
      - internal

  krayin_redis:
    image: redis:alpine
    networks:
      - internal

volumes:
  krayin_db_data:

networks:
  traefik_public:
    external: true
  internal:
    driver: overlay
```

---

## ⚙️ Configuração no Chatwoot

### 1. Configurar o Dashboard App (Barra Lateral de Atendimento)
1. No Chatwoot, vá em **Configurações ➔ Aplicativos do Painel (Dashboard Apps) ➔ Adicionar novo aplicativo**.
2. Preencha os campos:
   - **Nome**: `Krayin CRM`
   - **URL do Painel**: `https://crm.seu-dominio.com/chatwoot/embed`
3. Salve. Agora, ao abrir qualquer conversa no Chatwoot, a barra lateral direita exibirá os dados do lead no Krayin CRM.

### 2. Configurar o Webhook de Sincronização
1. No Chatwoot, vá em **Configurações ➔ Webhooks ➔ Adicionar novo webhook**.
2. Preencha:
   - **URL do Webhook**: `https://crm.seu-dominio.com/api/chatwoot/webhook`
   - **Eventos inscritos**: `contact_created`, `contact_updated`, `conversation_created`, `conversation_updated`.
3. Salve para ativar a sincronização em tempo real.

---

## 🛠️ Instalação Manual em um Krayin Existente

Se você já possui uma instalação do Krayin CRM rodando em código-fonte ou VPS própria e deseja apenas adicionar os módulos:

1. Clone ou copie as pastas dos módulos para o diretório `packages/Webkul/`:
   - `packages/Webkul/Chatwoot`
   - `packages/Webkul/Reports`

2. No `composer.json` principal do seu Krayin, adicione no bloco `autoload.psr-4`:
   ```json
   "autoload": {
       "psr-4": {
           "Webkul\\Chatwoot\\": "packages/Webkul/Chatwoot/src/",
           "Webkul\\Reports\\": "packages/Webkul/Reports/src/"
       }
   }
   ```

3. No arquivo `bootstrap/providers.php`, adicione os Service Providers:
   ```php
   return [
       // Outros providers...
       Webkul\Chatwoot\Providers\ChatwootServiceProvider::class,
       Webkul\Reports\Providers\ReportsServiceProvider::class,
   ];
   ```

4. Execute no terminal:
   ```bash
   composer dump-autoload --optimize
   php artisan optimize:clear
   ```

---

## 🔒 Variáveis de Ambiente

| Variável | Descrição | Exemplo |
| :--- | :--- | :--- |
| `APP_URL` | URL pública do seu CRM | `https://crm.empresa.com` |
| `CHATWOOT_URL` | URL pública da sua instância do Chatwoot | `https://chat.empresa.com` |
| `CHATWOOT_API_TOKEN` | Token de acesso de Administrador gerado no Chatwoot | `aBcD1234...` |
| `CHATWOOT_ACCOUNT_ID` | ID da conta no Chatwoot | `1` |

---

## 📄 Licença

Este projeto é disponibilizado sob a licença [MIT](LICENSE).
