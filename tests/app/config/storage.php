<?php

return [
    'default' => 'uploads',

    'servers' => [
        'static' => [
            'adapter' => 'local',
            'directory' => __DIR__,
        ],
    ],

    'buckets' => [
        'uploads' => [
            'server' => 'static',
            'prefix' => 'upload',
        ],
        'public' => [
            'server' => 'static',
            'prefix' => 'public',
        ],
    ],
];
