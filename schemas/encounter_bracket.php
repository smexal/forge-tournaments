<?php

return [
    'encounter_bracket',
    \i('Encounter Bracket', 'forge-tournaments'),
    ['encounter'],
    [
        [
            'type' => 'subsum',
            'key'  => 'matches_sum',
            'source' => 'system',
            'required' => 1,
            'field_config' => [
                'subkey' => 'winner',
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
                    ['left', '>', 'right', 1]
                ]
            ]
        ]
    ]
];