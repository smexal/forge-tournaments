<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases;

use \Forge\Modules\ForgeTournaments\Interfaces\IPhaseType;

class PerformancePhase extends BasePhase implements IPhaseType {
    public static function identifier() : string {
        return \Forge\Modules\ForgeTournaments\PhaseTypes::PERFORMANCE;
    }

    public static function name() : string {
        return i('Performance phase', 'forge-tournaments');
    }

    public function fields($item=null) : array {
        return [
            [
                'key' => 'amount_of_rounds',
                'label' => \i('Amount of Rounds', 'forge-tournaments'),
                'value' => '1',
                'multilang' => false,
                'type' => 'text',
                'order' => 4,
                'position' => 'right',
                'hint' => i('Defines the amount of rounds, which get played for this performance phase', 'forge-tournaments')
            ],
        ];
    }

}