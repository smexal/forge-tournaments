<?php

return [
    'match_simple_points',
    \i('Match Simple Points', 'forge-tournaments'),
    ['match'],
    [
        [
            'type' => 'integerN',
            'key'  => 'points',
            'source' => 'team',
            'required' => 1,
            'field_config' => [
                'not_same' => 1
            ]
        ],
        [
            'type' => 'comparison',
            'key'  => 'winner',
            'source' => 'system',
            'required' => 1,
            'field_config' => [
                'compare_key' => 'points',
                'conditions' => [
                    ['left', '<', 'right', 0],
                    ['left', '>', 'right', 2],
                    ['left', '=', 'right', 1],
                ]
            ]
        ]
    ]
];