<?php

return [
    'phase_result_group',
    \i('Phase result group', 'forge-tournaments'),
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
            'type' => 'subvalue',
            'key'  => 'qualified',
            'source' => 'system',
            'required' => 1,
            'field_config' => [
                'subkey' => 'qualified'
            ]
        ]
    ]
];