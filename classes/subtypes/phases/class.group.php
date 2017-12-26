<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases;

use Forge\Modules\ForgeTournaments\Interfaces\IPhaseType;

use  Forge\Modules\ForgeTournaments\PhaseState;
use Forge\Core\Classes\CollectionItem;
use Forge\Modules\ForgeTournaments\PoolRegistry;

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
                'position' => 'right',
                'hint' => i('', 'forge-tournaments'),
                '__last_phase_state' => PhaseState::CONFIG_PHASETYPE
            ]
        ];
    }

    public function modifyFields(array $fields, $item=null) : array {
        foreach($fields as &$field) {
            if($field['key'] == 'ft_slot_assignment') {
                $pool = PoolRegistry::instance()->getPool('phase');
                $phase = $pool->getInstance($item->id, $item);

                $field['group_size'] = $phase->getGroupSize();
                $field['sa_tpl'] = FORGE_TOURNAMENTS_DIR . 'templates/slotassignment-groups';
                $field['prepare_template'] = ['\\Forge\\Modules\\ForgeTournaments\\Fields\\SlotAssignment', 'prepareGroup'];
            }
        }
        return $fields;
    }

    public function render(CollectionItem $item) : string {
        $html = '';
        $phase = PoolRegistry::instance()->getPool('phase')->getInstance($item->id, $item);
        if($phase->getState() <= PhaseState::ASSIGNMENT) {
            return '';
        }
        $groups = $phase->getGroups();
        foreach($groups as $group) {
            $group_item = $group->getItem();
            $html .= "<h5>";
            $html .= "<a href=\"" . $group_item->url(true) . "\" target=\"_blank\">{$group_item->getName()}</a>";
            $html .= "</h5>";
            
            $html .= "<ul>";
            $participants = $group->getSlotAssignment();
            for($i = 0; $i < $participants->numSlots(); $i++) {
                $slot = $participants->getSlot($i);
                $slot_name = is_null($slot) ? 'EMPTY' : 'USED UP';

                $html .= '<li>';
                $html .= 'Slot ' . ($i + 1) .': ';
                $html .= '<span>' . $slot_name . '</span>';
                $html .= '</li>';
            }
            $html .= "</ul>";
            $html .= "<br />";
            $html .= "<br />";
        }
        return $html;
    }

}