# Imagem leve contendo estritamente os módulos/add-ons para Krayin CRM
FROM alpine:latest

# Copiar os pacotes dos módulos para o container
COPY packages/Webkul /modules/Webkul

# Comando para copiar os módulos para o volume compartilhado do Krayin
CMD ["sh", "-c", "echo 'Copiando módulos para o Krayin...' && mkdir -p /target && cp -rf /modules/Webkul/* /target/ && echo 'Módulos instalados com sucesso no volume!'"]
