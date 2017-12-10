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
                'position' => 'left',
                'hint' => i('', 'forge-tournaments'),
                '__last_phase_status' => PhaseState::FRESH
            ]
        ];
    }

    public function render(CollectionItem $item) : string {
        $html = '';
        $phase = PoolRegistry::instance()->getPool('phase')->getInstance($item->id, $item);
        $groups = $phase->getGroups();
        foreach($groups as $group) {
            $html .= "<h5>{$group->getItem()->getName()}</h5>";
            
            $html .= "<ul>";
            $participants = $group->getParticipantList();
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