<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases;

use \Forge\Modules\ForgeTournaments\Interfaces\IPhaseType;

class RegistrationPhase extends BasePhase implements IPhaseType {
    
    public static function identifier() : string {
        return \Forge\Modules\ForgeTournaments\PhaseTypes::REGISTRATION;
    }

    public static function name() : string {
        return i('Registration phase', 'forge-tournaments');
    }
    public function fields($item=null) : array {
        return [
            [
                'key' => 'custom_registration_field',
                'label' => \i('Custom registration Field', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                'type' => 'text',
                'order' => 100,
                'position' => 'left',
                'hint' => i('My Field only appears when i am a registration phase', 'forge-tournaments')
            ]
        ];
    }
}