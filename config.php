<?php
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'notifications',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    // Endpoint de teste para "simular" o FCM (pode ser trocado para o FCM real)
    'fcm_endpoint' => 'https://httpbin.org/post', // para testes (retorna o json enviado)
    'log_file' => __DIR__ . '/notification_scheduler.log',
];
