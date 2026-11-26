<?php
// Database settings come from the DATABASE_URL environment variable (set in Render)
$url = getenv('DATABASE_URL');

if ($url) {
    $p = parse_url($url);
    return [
        'host'     => $p['host'],
        'port'     => $p['port'] ?? 5432,
        'dbname'   => ltrim($p['path'], '/'),
        'user'     => urldecode($p['user']),
        'password' => urldecode($p['pass']),
    ];
}

// Fallback for local testing
return [
    'host'     => getenv('DB_HOST') ?: 'localhost',
    'port'     => getenv('DB_PORT') ?: 5432,
    'dbname'   => getenv('DB_NAME') ?: 'mdrrmo',
    'user'     => getenv('DB_USER') ?: 'postgres',
    'password' => getenv('DB_PASS') ?: '',
];
