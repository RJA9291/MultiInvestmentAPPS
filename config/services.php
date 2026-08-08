<?php

return [
    /**
     * Provider Manager (AI-014) configuration. `model` names the OpenAI
     * model string actually called by OpenAiProviderGateway — kept as a
     * literal config value here, deliberately NOT written into the
     * AI Model Registry (`ai_models`, DB-040) as an "Active" MODEL-XXX row.
     * 09_AI_ARCHITECTURE.md §25 keeps every MODEL-XXX "Not yet selected"
     * pending a real architecture decision; this key exists purely so the
     * gateway has something to call once a Prompt actually reaches Active,
     * and is not itself a governance decision.
     */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout_seconds' => (int) env('OPENAI_TIMEOUT_SECONDS', 30),
    ],
];
