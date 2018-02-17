<?php
define('FORGE_TOURNAMENT_NS', 'Forge\\Modules\\ForgeTournaments');
define('FORGE_TOURNAMENT_HOOK_NS', 'ForgeTournaments');
define('FORGE_TOURNAMENTS_DIR', MOD_ROOT . basename(dirname(__FILE__)) . '/');
define('FORGE_TOURNAMENTS_LIBS_DIR', FORGE_TOURNAMENTS_DIR . 'libs/');
define('FORGE_TOURNAMENTS_SCHEMAS_DIR', FORGE_TOURNAMENTS_DIR . 'schemas/');
define('FORGE_TOURNAMENTS_SCORINGS_DIR', FORGE_TOURNAMENTS_DIR . 'scorings/');
define('FORGE_TOURNAMENTS_COLLECTION_SUBTYPES', [
    'IPhaseType' => [
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Phases\\KOPhase',
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Phases\\GroupPhase',
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Phases\\PerformancePhase'
    ],
   'IParticipantType' => [
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Participants\\UserParticipant',
        '\\Forge\\Modules\\ForgeTournaments\\CollectionSubtypes\\Participants\\TeamParticipant'
    ]
]);