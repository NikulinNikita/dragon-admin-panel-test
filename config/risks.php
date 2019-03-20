<?php

return [
    'consumer' => [
        'topic' => [
            'name' => env('RISKS_CONSUMER_TOPIC_NAME', 'risk_management.output')
        ],

        'queue' => [
            'name' => env('RISKS_CONSUMER_QUEUE_NAME', 'risk_management.output')
        ],

        'binding' => [
            'key' => env('RISKS_CONSUMER_QUEUE_BINDING_KEY')
        ]
    ],
];
