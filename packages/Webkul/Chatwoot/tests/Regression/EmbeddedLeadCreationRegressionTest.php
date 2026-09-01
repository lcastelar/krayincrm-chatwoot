<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
$controller = file_get_contents($root . '/src/Http/Controllers/ChatwootEmbedController.php');
$view = file_get_contents($root . '/src/Resources/views/embed.blade.php');

if ($controller === false || $view === false) {
    throw new RuntimeException('Arquivos do fluxo de criação de negócio não foram encontrados.');
}

foreach ([
    "DB::table('lead_sources')" => 'O controller deve carregar as origens cadastradas.',
    "'sources' => \$sources" => 'A busca deve expor as origens para o iframe.',
    "'lead_source_id' => \$request->input('lead_source_id')" => 'A origem selecionada deve ser persistida.',
    "id=\"new-lead-source\"" => 'O formulário deve oferecer o seletor de origem.',
    'Negócio com ${personName}' => 'O formulário deve sugerir o título baseado no contato.',
    'lead_source_id: document.getElementById(\'new-lead-source\').value' => 'O navegador deve enviar a origem selecionada.',
] as $needle => $message) {
    if (! str_contains($controller . "\n" . $view, $needle)) {
        throw new RuntimeException($message);
    }
}

echo "Embedded lead creation defaults are present.\n";
