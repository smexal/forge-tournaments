<?php

return [
    'team_standard',
    \i('Team standard', 'forge-tournaments'),
    [
        'participants' => \Forge\Modules\ForgeTournaments\ParticipantTypes::TEAM,
        'match_handling' => 'versus',
        'phase_types' => [
            'group' =>  [
                'schemas' => [
                    'phase' => 'phase_result_group',
                    'group' => 'group_group',
                    'encounter' => 'encounter_points_winner',
                    'match' => 'match_simple_points'
                ]
            ],
            'bracket' =>  [
                'schemas' => [
                    'phase' => 'phase_result_bracket',
                    'group' => 'group_bracket',
                    'encounter' => 'encounter_bracket',
                    'match' => 'match_simple_points'
                ],
            ]
        ]
    ]
];