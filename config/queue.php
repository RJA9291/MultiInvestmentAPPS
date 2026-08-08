<?php

/**
 * `sync` (no real queue) is the .env.example default for the MVP soft
 * launch — every `ShouldQueue` Listener/Job this Sprint (RecordContentView,
 * TriggerMatchingOnInvestorVerified, RunAiCompliancePrecheckOnSubmit, etc.)
 * still runs, just synchronously in-request rather than backgrounded.
 * Switch to `database` (simplest real queue, no extra infra) once real
 * usage makes synchronous AI/notification calls noticeably slow.
 */
return [
    'default' => env('QUEUE_CONNECTION', 'sync'),

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],
        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CONNECTION'),
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 90,
        ],
    ],
];
