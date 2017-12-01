<?php

return [
    'encounter_points_winner',
    \i('Encounter Points Winner', 'forge-tournaments'),
    ['encounter'],
    [
        [
            'type' => 'integerN',
            'key'  => 'matches_sum',
            'source' => 'participant',
            'required' => 1,
            'field_config' => [
                'boN' => 3,
                'validators' => [
                    'boNValidator::validate'
                ]
            ]
        ],
        [
            'type' => 'comparison',
            'key'  => 'winner',
            'source' => 'system',
            'required' => 1,
            'field_config' => [
                'compare_key' => 'matches_sum',
                'conditions' => [
                    ['left', '<', 'right', 0],
                    ['left', '>', 'right', 2],
                    ['left', '=', 'right', 1],
                ]
            ]
        ]
    ]
];