# Base oficial do Krayin CRM
FROM webkul/krayin:latest

USER root

# 1. Copiar pacotes customizados (Chatwoot & Relatórios)
COPY packages/Webkul/Chatwoot /var/www/laravel-crm/packages/Webkul/Chatwoot
COPY packages/Webkul/Reports /var/www/laravel-crm/packages/Webkul/Reports

WORKDIR /var/www/laravel-crm

# 2. Registrar os Service Providers e Namespaces PSR-4 no Composer e Laravel
RUN php -r ' \
    $composer = json_decode(file_get_contents("composer.json"), true); \
    $composer["autoload"]["psr-4"]["Webkul\\\\Chatwoot\\\\"] = "packages/Webkul/Chatwoot/src/"; \
    $composer["autoload"]["psr-4"]["Webkul\\\\Reports\\\\"] = "packages/Webkul/Reports/src/"; \
    file_put_contents("composer.json", json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)); \
    \
    $providersFile = "bootstrap/providers.php"; \
    if (file_exists($providersFile)) { \
        $content = file_get_contents($providersFile); \
        if (!str_contains($content, "ChatwootServiceProvider")) { \
            $content = preg_replace("/\];/", "    Webkul\\\\Chatwoot\\\\Providers\\\\ChatwootServiceProvider::class,\n    Webkul\\\\Reports\\\\Providers\\\\ReportsServiceProvider::class,\n];", $content); \
            file_put_contents($providersFile, $content); \
        } \
    } \
'

# 3. Otimizar autoload do Composer
RUN composer dump-autoload --optimize --no-interaction && \
    chown -R www-data:www-data /var/www/laravel-crm/packages && \
    chmod -R 755 /var/www/laravel-crm/packages

EXPOSE 80

CMD ["php", "artisan", "octane:start", "--server=frankenphp", "--host=0.0.0.0", "--port=80"]
