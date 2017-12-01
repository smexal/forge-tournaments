<?php

return [
    'phase_result_bracket',
    \i('Phase result bracket', 'forge-tournaments'),
    ['phase'],
    [
        [
            'type' => 'subvalue',
            'key'  => 'phase_points',
            'source' => 'system',
            'required' => 1,
            'field_config' => [
                'subkey' => 'group_points'
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