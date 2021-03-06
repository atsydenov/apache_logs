<?php
return [
    'logs' => [
        '/var/www/tmp/log_1',
        '/var/www/tmp/log_2',
        '/var/www/tmp/log_3'
    ],
    'formatLogs' => 'combined',
    'fileMask' => 'access.log',
    'timezone' => '+0600',
    'parsePeriod' => [
        'min' => '*',
        'hour' => '*',
        'dayMonth' => '*',
        'month' => '*',
        'weekDay' => '*',
    ],
];