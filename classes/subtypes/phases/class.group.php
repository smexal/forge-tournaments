<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases;

use \Forge\Modules\ForgeTournaments\Interfaces\IPhaseType;

use \Forge\Core\Classes\CollectionItem;

class GroupPhase extends BasePhase implements IPhaseType {

    public static function identifier() : string {
        return \Forge\Modules\ForgeTournaments\PhaseType::GROUP;
    }

    public static function name() : string {
        return i('Group phase', 'forge-tournaments');
    }

    public function fields($item=null) : array {
        return [
            [
                'key' => 'custom_group_field',
                'label' => \i('Custom Group Field', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                'type' => 'text',
                'order' => 100,
                'position' => 'left',
                'hint' => i('My Field only appears when i am a group phase', 'forge-tournaments')
            ]
        ];
    }

    public function render(CollectionItem $item) : string {
        return '<div style="color:red">MY Name is dem super duper GroupPhase</div>';
    }

}