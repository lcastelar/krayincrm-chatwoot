<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chatwoot Embed & Integration Configuration
    |--------------------------------------------------------------------------
    */
    'embed_secret' => env('CHATWOOT_EMBED_SECRET', ''),
    'webhook_secret' => env('CHATWOOT_WEBHOOK_SECRET', ''),
    'chatwoot_url' => env('CHATWOOT_URL', ''),
    'chatwoot_api_access_token' => env('CHATWOOT_API_TOKEN', ''),
    'api_token' => env('KRAYIN_API_TOKEN', env('CHATWOOT_API_TOKEN', '')),

    'default_pipeline_id' => env('CHATWOOT_DEFAULT_PIPELINE_ID', 2),
    'default_stage_id' => env('CHATWOOT_DEFAULT_STAGE_ID', 11),
    'default_user_id' => env('CHATWOOT_DEFAULT_USER_ID', 1),
];
