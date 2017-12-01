<?php

return [
    'group_bracket',
    \i('Group Bracket', 'forge-tournaments'),
    ['group'],
    [
       [
            'type' => 'subsum',
            'key'  => 'group_points',
            'source' => 'system',
            'required' => 1,
            'field_config' => [
                'subkey' => 'winner'
            ]
        ],
        [
            'type' => 'order',
            'key'  => 'order',
            'source' => 'system',
            'required' => 1,
            'field_config' => [
                'key' => 'group_points',
                'direction' => 'DESC'
            ]
        ],
    ]
];