<?php

namespace Forge\Modules\ForgeTournaments\Phases;

use \Forge\Modules\ForgeTournaments\Interfaces\IPhaseType;

class PerformancePhase extends BasePhase implements IPhaseType {
    public static function identifier() : string {
        return \Forge\Modules\ForgeTournaments\PhaseType::PERFORMANCE;
    }

    public static function name() : string {
        return i('Performance phase', 'forge-tournaments');
    }

    public function fields($item=null) : array {
        return [
            [
                'key' => 'custom_PERFORMANCE_field',
                'label' => \i('Custom PERFORMANCE Field', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                'type' => 'text',
                'order' => 100,
                'position' => 'left',
                'hint' => i('My Field only appears when i am a PERFORMANCE phase', 'forge-tournaments')
            ]
        ];
    }

}