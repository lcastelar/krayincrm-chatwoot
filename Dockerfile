# Imagem leve contendo estritamente os módulos/add-ons para Krayin CRM
FROM alpine:latest

# Copiar os pacotes dos módulos para o container
COPY packages/Webkul /modules/Webkul

# Garantir permissões de leitura pública (755) para que o PHP-FPM (www-data) possa ler os arquivos
RUN chmod -R 755 /modules/Webkul

# Comando para copiar os módulos para o volume compartilhado do Krayin
CMD ["sh", "-c", "echo 'Copiando módulos para o Krayin...' && mkdir -p /target && cp -rf /modules/Webkul/* /target/ && chmod -R 755 /target && echo 'Módulos instalados com sucesso no volume!'"]
