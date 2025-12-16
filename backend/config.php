<?php
declare(strict_types=1);

/**
 * Application configuration.
 *
 * NOTE: For production, set APP_SECRET via environment variable.
 */
return [
    // Secret used to sign access tokens
    'APP_SECRET' => getenv('APP_SECRET') ?: 'CHANGE_ME_TO_A_LONG_RANDOM_SECRET',

    // Token validity in seconds (default: 7 days)
    'TOKEN_TTL'  => (int)(getenv('TOKEN_TTL') ?: 60 * 60 * 24 * 7),

    /**
     * Optional: comma-separated list of admin emails.
     * Example: ADMIN_EMAILS="admin@example.com,other@example.com"
     */
    'ADMIN_EMAILS' => array_values(array_filter(array_map('trim', explode(',', getenv('ADMIN_EMAILS') ?: '')))),
];
