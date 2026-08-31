<?php

declare(strict_types=1);

$controller = file_get_contents(dirname(__DIR__, 2).'/src/Http/Controllers/ChatwootWebhookController.php');

if ($controller === false) {
    fwrite(STDERR, "Unable to read ChatwootWebhookController.php\n");

    exit(1);
}

/**
 * Extract a protected controller method without requiring a full Laravel bootstrap.
 */
function controllerMethod(string $source, string $name): string
{
    $start = strpos($source, "protected function {$name}");
    $end = strpos($source, "\n    /**", $start + 1);

    if ($start === false || $end === false) {
        fwrite(STDERR, "Unable to locate {$name}()\n");

        exit(1);
    }

    return substr($source, $start, $end - $start);
}

foreach (['syncPersonTags', 'syncTagDeleted'] as $method) {
    if (str_contains(controllerMethod($controller, $method), 'lead_tags')) {
        fwrite(STDERR, "{$method}() must not synchronize Chatwoot tags to leads.\n");

        exit(1);
    }
}

echo "Chatwoot tags are restricted to contacts.\n";
