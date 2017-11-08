<?php
define('FORGE_TOURNAMENT_NS', 'Forge\\Modules\\ForgeTournaments');
define('FORGE_TOURNAMENT_HOOK_NS', 'ForgeTournaments');
define('FORGE_TOURNAMENTS_DIR', MOD_ROOT . basename(dirname(__FILE__)) . '/');
define('FORGE_TOURNAMENTS_LIBS_DIR', FORGE_TOURNAMENTS_DIR . 'libs/');

define('FORGE_TOURNAMENTS_COLLECTION_SUBTYPES', [
    'IPhaseType' => [
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Phases\\KOPhase',
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Phases\\GroupPhase',
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Phases\\RegistrationPhase',
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Phases\\PerformancePhase'
    ],
   'IParticipantType' => [
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Participants\\UserParticipant',
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Participants\\TeamParticipant'
    ]
]);

define('FORGE_TOURNAMENTS_SCORINGS', [
    'egoshooter_group_bo3' => [
        'phase' => [
            'type' => 'group',
            'encounter_type' => 'versus'
        ],
        'groups' => [
            'sorting' => [
                'type' => 'byValue',
                'params' => ['encounter_won', 'asc']
            ]
        ],
        'encounters' => [
            // Called after data for match is set
            // Inform groups if an encounter is resolved
            'handler' => [
                'type' => 'bestOfN',
                'params' => [
                    // HERE COMES N (By System)
                    3
                ],
            ],
            'fields' => [
                'key' => 'matches_points',
                'type' => 'integer',
                'access' => 'system',
                'source' => [
                    'type' => 'subsum',
                    'params' => [
                        'children.win_lose'
                    ]
                ],
                'key' => 'encounter_won',
                'type' => 'boolean',
                'access' => 'system',
                'source' => [
                    'type' => 'compareSetValBool',
                    'params' => [
                        'matches_points',
                        // LOSE, WIN
                        [0 => 0, 1 => 1]
                    ]
                ],
            ]
        ],
        'matches' => [
            'fields' => [
                [
                    'key' => 'points',
                    'type' => 'integer',
                    'access' => 'team'
                ],
                [
                    'key' => 'win_lose',
                    'type' => 'integer',
                    // System, Admin, Coach
                    'access' => 'admin',
                    'source' => [
                        'type' => 'compareSetValTri',
                        'params' => [
                            'points',
                            // LOSE, DRAW, WIN
                            [-1 => 0, 0 => 1, 1 => 2]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'egoshooter_bracket_bo3' => [
        'phase' => [
            'type' => 'bracket',
            'encounter_type' => 'versus'
        ],
        'groups' => [
            // Called after data for encounter is set
            'handler' => [
                'type' => 'bracket',
                'params' => [
                    // Standard, Double Elimination usw.
                    'standard'
                ],
            ],
        ],
        'encounters' => [
            // Called after data for match is set
            // Inform
            'handler' => [
                'type' => 'bestOfN',
                'params' => [
                    // HERE COMES N (By System)
                    3
                ],
            ],
            'fields' => [
                'key' => 'matches_points',
                'type' => 'integer',
                'access' => 'system',
                'source' => [
                    'type' => 'subsum',
                    'params' => [
                        'children.win_lose'
                    ]
                ],
                'key' => 'encounter_won',
                'type' => 'boolean',
                'access' => 'system',
                'source' => [
                    'type' => 'compareSetValBool',
                    'params' => [
                        'matches_points',
                        // LOSE, WIN
                        [0 => 0, 1 => 1]
                    ]
                ],
            ]
        ],
        'matches' => [
            'fields' => [
                [
                    'key' => 'points',
                    'type' => 'integer',
                    'access' => 'team'
                ],
                [
                    'key' => 'win_lose',
                    'type' => 'integer',
                    // System, Admin, Coach
                    'access' => 'admin',
                    'source' => [
                        'type' => 'compareSetValTri',
                        'params' => [
                            'points',
                            // LOSE, DRAW, WIN
                            [-1 => 0, 0 => 1, 1 => 2]
                        ]
                    ]
                ]
            ]
        ]
    ],
]);