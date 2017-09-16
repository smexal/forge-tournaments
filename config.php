<?php
define('FORGE_TOURNAMENT_NS', 'Forge\\Modules\\ForgeTournaments');
define('FORGE_TOURNAMENT_HOOK_NS', 'ForgeTournaments');
define('FORGE_TOURNAMENTS_DIR', MOD_ROOT . basename(dirname(__FILE__)) . '/');

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