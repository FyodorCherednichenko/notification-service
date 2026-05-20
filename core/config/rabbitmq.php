<?php

return [
    'host' => env('RABBITMQ_HOST', 'rabbit_mq'),
    'port' => env('RABBITMQ_PORT', 5672),
    'user' => env('RABBITMQ_LOGIN', 'rabbitmq'),
    'password' => env('RABBITMQ_PASSWORD', null),
    'vhost' => env('RABBITMQ_VHOST', '/'),
    'queue' => env('RABBITMQ_QUEUE', 'default'),
];
