<?php

return [
    'user_performance',
    \i('User performance', 'forge-tournaments'),
    [
        'participants' => \Forge\Modules\ForgeTournaments\ParticipantTypes::USER,
        'match_handling' => 'performance',
        'phase_types' => [
            'performance' =>  [
                'schemas' => [
                    'phase' => 'phase_result_group',
                    'group' => 'group_group',
                    'encounter' => 'encounter_points_winner',
                    'match' => 'match_trackmania'
                ]
            ]
        ]
    ]
];