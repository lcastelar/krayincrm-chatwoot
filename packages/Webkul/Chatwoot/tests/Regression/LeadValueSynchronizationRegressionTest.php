<?php

declare(strict_types=1);

$packageRoot = dirname(__DIR__, 2);
$servicePath = $packageRoot.'/src/Services/LeadValueSynchronizer.php';
$controllerPath = $packageRoot.'/src/Http/Controllers/ChatwootApiController.php';
$migrationPath = $packageRoot.'/src/Database/Migrations/2026_08_31_000001_create_lead_value_sync_triggers.php';

foreach ([$servicePath, $controllerPath, $migrationPath] as $path) {
    if (! is_file($path)) {
        fwrite(STDERR, "Missing lead value synchronization artifact: {$path}\n");

        exit(1);
    }
}

$service = file_get_contents($servicePath);
$controller = file_get_contents($controllerPath);
$migration = file_get_contents($migrationPath);

if ($service === false || $controller === false || $migration === false) {
    fwrite(STDERR, "Unable to read lead value synchronization artifacts.\n");

    exit(1);
}

foreach ([
    [$service, 'CALL chatwoot_recalculate_lead_value(?)'],
    [$service, "->value('lead_value')"],
    [$controller, 'DB::transaction'],
    [$controller, 'recalculateLeadValue'],
    [$controller, "'lead_value' => \$leadValue"],
    [$migration, 'AFTER INSERT'],
    [$migration, 'AFTER UPDATE'],
    [$migration, 'AFTER DELETE'],
    [$migration, 'SUM(amount) FROM lead_products'],
] as [$source, $expected]) {
    if (! str_contains($source, $expected)) {
        fwrite(STDERR, "Expected lead value synchronization behavior is missing: {$expected}\n");

        exit(1);
    }
}

echo "Lead values are synchronized from lead products.\n";
