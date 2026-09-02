<?php

declare(strict_types=1);

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

$appRoot = getenv('KRAYIN_APP_ROOT') ?: dirname(__DIR__, 6);

require $appRoot.'/vendor/autoload.php';
$app = require $appRoot.'/bootstrap/app.php';
$app->instance('request', Request::create('/'));
$app->make(Kernel::class)->bootstrap();

config(['chatwoot.krayin_api_token' => 'regression-test-token']);

$kernel = $app->make(Kernel::class);
$request = static function (string $method, string $uri, array $payload = [], ?string $token = 'regression-test-token') use ($kernel) {
    $server = ['HTTP_ACCEPT' => 'application/json'];

    if ($token !== null) {
        $server['HTTP_AUTHORIZATION'] = 'Bearer '.$token;
    }

    $httpRequest = Request::create($uri, $method, [], [], [], $server, json_encode($payload, JSON_THROW_ON_ERROR));
    $httpRequest->headers->set('Content-Type', 'application/json');

    return $kernel->handle($httpRequest);
};

$assertStatus = static function ($response, int $expected, string $context): array {
    if ($response->getStatusCode() !== $expected) {
        throw new RuntimeException(sprintf('%s: esperado HTTP %d, recebido HTTP %d (%s)', $context, $expected, $response->getStatusCode(), $response->getContent()));
    }

    return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
};

$suffix = bin2hex(random_bytes(6));
$skuV1 = 'regression-v1-'.$suffix;
$skuAdmin = 'regression-admin-'.$suffix;

DB::beginTransaction();

try {
    $assertStatus($request('POST', '/api/v1/products', ['name' => 'Sem autenticação', 'sku' => $skuV1]), 401, 'autenticação ausente');
    $assertStatus($request('POST', '/api/v1/products', ['name' => 'Token inválido', 'sku' => $skuV1], 'invalid-test-token'), 401, 'autenticação inválida');

    $created = $assertStatus($request('POST', '/api/v1/products', [
        'name' => 'Produto temporário V1',
        'sku' => $skuV1,
        'description' => 'Criado por teste transacional',
        'quantity' => 3,
        'price' => 19.95,
    ]), 201, 'criação v1');

    foreach (['id', 'name', 'sku', 'description', 'quantity', 'price', 'created_at', 'updated_at'] as $field) {
        if (! array_key_exists($field, $created['data'] ?? [])) {
            throw new RuntimeException('Campo ausente na criação: '.$field);
        }
    }

    if ((int) $created['data']['quantity'] !== 3 || (float) $created['data']['price'] !== 19.95) {
        throw new RuntimeException('Preço ou quantidade não foram persistidos na criação.');
    }

    $assertStatus($request('POST', '/api/v1/products', ['name' => 'Duplicado', 'sku' => $skuV1]), 422, 'SKU duplicado na criação');

    $updated = $assertStatus($request('PUT', '/api/v1/products/'.$created['id'], [
        'name' => 'Produto temporário atualizado',
        'sku' => $skuV1,
        'quantity' => 7,
        'price' => 29.90,
    ]), 200, 'atualização v1');

    if ((int) $updated['data']['quantity'] !== 7 || (float) $updated['data']['price'] !== 29.90) {
        throw new RuntimeException('Preço ou quantidade não foram persistidos corretamente.');
    }

    $admin = $assertStatus($request('POST', '/api/admin/products', ['name' => 'Produto temporário Admin', 'sku' => $skuAdmin]), 201, 'criação admin');
    $assertStatus($request('PUT', '/api/admin/products/'.$admin['id'], [
        'name' => 'Produto temporário Admin atualizado',
        'sku' => $skuAdmin,
        'quantity' => 4,
        'price' => 12.50,
    ]), 200, 'atualização admin');
    $assertStatus($request('PUT', '/api/admin/products/'.$created['id'], ['name' => 'Conflito', 'sku' => $skuAdmin]), 422, 'SKU duplicado na atualização');
    $assertStatus($request('PUT', '/api/v1/products/999999999', ['name' => 'Inexistente', 'sku' => 'missing-'.$suffix]), 404, 'produto inexistente');
} finally {
    DB::rollBack();
}

echo "Product mutation API regression passed.\n";
