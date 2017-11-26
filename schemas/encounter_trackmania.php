<?php

return [
    'encounter_trackmania',
    \i('Encounter Trackmania', 'forge-tournaments'),
    ['encounter'],
    [
        [
            'type' => 'trackmania_time_adapter',
            'key'  => 'time',
            'source' => 'system',
            'required' => 1,
            'field_config' => []
        ],
        /**
         * 1. convert time to points
         * 
         */
    ]
];