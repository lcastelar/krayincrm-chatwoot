# Krayin CRM Customizado (Chatwoot + Relatórios & Inteligência)

Este repositório contém a imagem Docker personalizada do **Krayin CRM** com os módulos:
- 💬 **`Webkul\Chatwoot`**: Dashboard App para agentes no Chatwoot, webhooks de sincronização e automação.
- 📊 **`Webkul\Reports`**: Painel avançado de Relatórios & Inteligência de Vendas (Visão Geral, Vendas & Receita, Funil & Conversão Cônico, Desempenho por Tags e Produtividade de Equipe).

---

## 📁 Estrutura do Repositório

```text
krayin-custom-crm/
├── .github/
│   └── workflows/
│       └── docker-build.yml     # Pipeline automático de Build e Push para o GitHub Container Registry
├── packages/
│   └── Webkul/
│       ├── Chatwoot/            # Módulo Chatwoot Dashboard & Webhook
│       └── Reports/             # Módulo de Relatórios & Inteligência de Vendas
├── Dockerfile                   # Constrói a imagem pronta com os pacotes registrados
├── stack.yml                    # Stack para rodar no Portainer / Docker Swarm
├── .dockerignore
├── .gitignore
└── README.md
```

---

## 🚀 Como Subir para o seu GitHub (Passo a Passo)

### 1. Criar o Repositório no GitHub
1. Acesse [github.com/new](https://github.com/new).
2. Dê um nome ao repositório, por exemplo: `krayin-crm` (privado ou público).
3. **Não** marque para inicializar com README ou .gitignore (já temos tudo pronto).

### 2. Inicializar o Git e Fazer o Push
Abra o terminal dentro desta pasta (`krayin-custom-crm`) e execute:

```bash
git init
git add .
git commit -m "feat: Krayin CRM com módulos Chatwoot e Reports"
git branch -M main
git remote add origin https://github.com/SEU_USUARIO/krayin-crm.git
git push -u origin main
```

*(Substitua `SEU_USUARIO` pelo seu usuário ou organização no GitHub)*.

---

## ⚙️ Configurar Permissões do Pacote no GitHub (Importante)

1. Assim que o push for feito, a aba **Actions** do GitHub começará a compilar a imagem automaticamente.
2. Quando concluir, o pacote aparecerá na página inicial do repositório em **Packages** (`ghcr.io/SEU_USUARIO/krayin-crm`).
3. Clique no pacote ➔ **Package Settings** ➔ role até o final em **Danger Zone**:
   - Se o repositório for privado e você quiser puxar a imagem no Portainer sem autenticação de token, você pode alterar a visibilidade do pacote para **Public**.
   - Ou no Portainer, crie um **Registry Credential** com seu usuário do GitHub e um Personal Access Token (`read:packages`).

---

## 🐳 Como Usar na Stack do Portainer

1. Abra o arquivo `stack.yml`.
2. Substitua a linha:
   ```yaml
   image: ghcr.io/SEU_USUARIO/krayin-crm:latest
   ```
   pelo caminho real da sua imagem no GitHub.
3. Copie o conteúdo de `stack.yml` e cole no seu **Portainer** em **Stacks ➔ Add Stack / Editor**.
4. Clique em **Deploy the stack**.

Pronto! Qualquer alteração futura que você enviar com `git push` vai gerar uma nova versão da imagem automaticamente pelo GitHub Actions.
