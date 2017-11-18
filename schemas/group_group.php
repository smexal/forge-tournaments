<?php

return [
    'group_group',
    \i('Group group', 'forge-tournaments'),
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
        [
            'type' => 'isTopN',
            'key'  => 'qualified',
            'source' => 'system',
            'required' => 1,
            'field_config' => [
                'size' => 8 // Will have to be set from the collection data of the phase
            ]
        ],
    ]
];