<?php

return [
    'kafka' => [
        'driver' => 'kafka',
        'connection' => env('KAFKA_QUEUE_CONNECTION', 'default'),

        'broker_list' => env('KAFKA_BROKER_LIST', 'kafka:9092'),
        'queue' => env('KAFKA_QUEUE', 'default'),
        'heartbeat' => 5 * 1000,
        'group_name' => env('KAFKA_QUEUE_GROUP', 'default'),
        'producer_timeout' => 1 * 1000,
        'consumer_timeout' => 3 * 1000,
        'auth_login' => env('KAFKA_AUTH_LOGIN'),
        'auth_mechanism' => env('KAFKA_AUTH_MECHANISM', 'PLAIN'),
        'auth_password' => env('KAFKA_AUTH_PASSWORD'),
        'auth_ssl_ca_location' => env('KAFKA_AUTH_SSL_CA_LOCATION'),
    ],
];
