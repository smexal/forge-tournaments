<?php

namespace Forge\Modules\ForgeTournaments\Fields;

use Forge\Core\Classes\Fields;
use Forge\Modules\ForgeTournaments\Utils;
use Forge\Modules\ForgeTournaments\PhaseTypes;
use Forge\Modules\ForgeTournaments\PhaseState;

abstract class FieldProvider {

    public static function phaseTypeSelect($overrides=[]) {
       return array_merge([
            'key' => 'ft_phase_type',
            'type' => 'select',
            'label' => \i('Phase type', 'forge-tournaments'),
            'values' => Utils::getPhaseTypes(),
            'value' => PhaseTypes::REGISTRATION,
            'multilang' => false,
            'order' => 4,
            'position' => 'right',
            'hint' => i('Select the phase type', 'forge-tournaments'),
        ], $overrides);
    }
}