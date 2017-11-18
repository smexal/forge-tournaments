<?php

return [
    'match_trackmania',
    \i('Match Trackmania', 'forge-tournaments'),
    ['match'],
    [
        [
            'type' => 'trackmania_time_adapter',
            'key'  => 'time',
            'source' => 'system',
            'required' => 1,
            'field_config' => []
        ]
    ]
];