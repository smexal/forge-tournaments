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
                'key' => 'single_double',
                'label' => \i('Double or Sigle Elimination?', 'forge-tournaments'),
                'value' => '',
                'multilang' => false,
                'type' => 'select',
                'values' => [
                    'single' => i('Single Elimination', 'forge-tournaments'),
                    'double' => i('Double Eliminiation', 'forge-tournaments')
                ],
                'order' => 3,
                'position' => 'right',
                'hint' => i('Choose your bracket Type', 'forge-tournaments')
            ]
        ];
    }

    public function modifyFields(array $fields, $item = null) : array {
        foreach($fields as &$field) {
            if($field['key'] == 'ft_slot_assignment') {
                $field['prepare_template'] = ['\\Forge\\Modules\\ForgeTournaments\\Fields\\SlotAssignment', 'prepareKO'];
                $field['sa_tpl'] = FORGE_TOURNAMENTS_DIR . 'templates/slotassignment-ko';
                $field['order'] = 40;
            }
        }
        return $fields;
    }

}