<?php

return [
    'team_standard',
    \i('Team standard', 'forge-tournaments'),
    [
        'participants' => \Forge\Modules\ForgeTournaments\ParticipantTypes::TEAM,
        'encounter_handling' => 'versus',
        'match_handling' => 'none',
        'phase_types' => [
            'group' =>  [
                'schemas' => [
                    'phase' => 'phase_result_group',
                    'group' => 'group_group',
                    'encounter' => 'encounter_points_winner'
                ]
            ],
            'ko' =>  [
                'schemas' => [
                    'phase' => 'phase_result_bracket',
                    'group' => 'group_bracket',
                    'encounter' => 'encounter_bracket'
                ],
            ]
        ]
    ]
];