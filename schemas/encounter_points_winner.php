<?php

return [
    'encounter_points_winner',
    [
        [
            'type' => 'subsum',
            'key'  => 'matches_sum',
            'source' => 'system',
            'required' => 1,
            'field_config' => [
                'subkey' => 'winner',
                'conditions' => [
                    ['left', '<', 'right', 0],
                    ['left', '>', 'right', 2],
                    ['left', '=', 'right', 1],
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