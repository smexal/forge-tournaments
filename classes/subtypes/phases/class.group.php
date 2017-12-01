<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases;

use \Forge\Modules\ForgeTournaments\Interfaces\IPhaseType;

use \ Forge\Modules\ForgeTournaments\PhaseState;
use \Forge\Core\Classes\CollectionItem;

class GroupPhase extends BasePhase implements IPhaseType {

    public static function identifier() : string {
        return \Forge\Modules\ForgeTournaments\PhaseTypes::GROUP;
    }

    public static function name() : string {
        return i('Group phase', 'forge-tournaments');
    }

    public function fields($item=null) : array {
        return [
            [
                'key' => 'ft_group_size',
                'label' => \i('How many participants per group?', 'forge-tournaments'),
                'value' => 4,
                'multilang' => false,
                'type' => 'number',
                'order' => 100,
                'position' => 'left',
                'hint' => i('', 'forge-tournaments'),
                '__last_phase_state' => PhaseState::FRESH
            ]
        ];
    }

    public function render(CollectionItem $item) : string {
        return '<div style="color:red">MY Name is dem super duper GroupPhase</div>';
    }

}