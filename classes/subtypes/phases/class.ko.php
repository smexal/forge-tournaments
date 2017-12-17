<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases;

use \Forge\Modules\ForgeTournaments\Interfaces\IPhaseType;

class KOPhase extends BasePhase implements IPhaseType {
    public static function identifier() : string  {
        return \Forge\Modules\ForgeTournaments\PhaseTypes::KOSYSTEM;
    }

    public static function name() : string  {
        return i('KO phase', 'forge-tournaments');
    }

    public function fields($item=null) : array {
        return [
            [
                'key' => 'custom_ko_field',
                'label' => \i('Custom KO Field', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                'type' => 'text',
                'order' => 100,
                'position' => 'left',
                'hint' => i('My Field only appears when i am a KO phase', 'forge-tournaments')
            ]
        ];
    }

    public function modifyFields(array $fields, $item = null) : array {
        foreach($fields as &$field) {
            if($field['key'] == 'ft_slot_assignment') {
                $field['prepare_template'] = ['\\Forge\\Modules\\ForgeTournaments\\Fields\\SlotAssignment', 'prepareKO'];
                $field['sa_tpl'] = FORGE_TOURNAMENTS_DIR . 'templates/slotassignment-ko';
            }
        }
        return $fields;
    }

}