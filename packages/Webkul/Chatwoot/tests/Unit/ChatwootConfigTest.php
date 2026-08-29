<?php

namespace Webkul\Chatwoot\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ChatwootConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('KRAYIN_API_TOKEN');
        putenv('CHATWOOT_API_TOKEN');

        parent::tearDown();
    }

    public function test_api_token_uses_the_chatwoot_token_when_both_tokens_exist(): void
    {
        putenv('KRAYIN_API_TOKEN=krayin-token-fixture');
        putenv('CHATWOOT_API_TOKEN=chatwoot-token-fixture');

        $config = require dirname(__DIR__, 2).'/src/Config/chatwoot.php';

        self::assertSame('chatwoot-token-fixture', $config['api_token']);
    }
}
