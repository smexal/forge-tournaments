<?php

namespace Forge\Modules\ForgeTournaments\CollectionSubtypes\Phases;

use Forge\Modules\ForgeTournaments\Interfaces\IPhaseType;

use Forge\Core\Classes\Media;
use Forge\Modules\ForgeTournaments\PhaseState;
use Forge\Core\Classes\CollectionItem;
use Forge\Modules\ForgeTournaments\PoolRegistry;
use Forge\Modules\ForgeTournaments\Output;
use Forge\Modules\ForgeTournaments\Data\DataSegment;
use Forge\Modules\ForgeTournaments\Data\DataSet;
use Forge\Modules\ForgeTournaments\Data\DatasetStorage;
use Forge\Modules\ForgeTournaments\ParticipantCollection;
use Forge\Modules\TournamentsTeams\TeamsCollection;

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
                $field['order'] = 40;
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
            $html .= Output::participantList($group->getSlotAssignment());
            $html .= "<br />";
            $html .= "<br />";
        }
        return $html;
    }

    public function populateOutput() {
        $groupNo = 1;
        $groupId = 'A';
        $encounters = [];
        $standings = [];
        $amountToNextPhase = $this->parentPhase->getMeta('ft_num_winners');
        foreach($this->parentPhase->getGroups() as $group) {
            $encounters = array_merge($encounters, $group->getEncounters());

            $standings[] = [
                'no' => $groupNo,
                'title' => i(sprintf('Group %1$s', $groupId), 'forge-tournaments'),
                'values' => $this->getGroupStandingValues($group)
            ];

            $groupNo++;
            $groupId++;
        }
        $outputList = [];
        foreach($standings as $group) {
            $nextCount = 0;
            foreach($group['values'] as $groupStanding) {
                if($nextCount < $amountToNextPhase) {
                    $outputList[] = $groupStanding['participantID'];
                } else {
                    break;
                }
                $nextCount++;
            }
        }
        $this->parentPhase->getItem()->updateMeta('ft_participant_output_list', implode(",", $outputList), 0);
    }

    public function getGroupStandingValues($group) {
        $position = 1;
        $values = [];
        foreach($group->getStandings() as $standingEntry) {
            if(is_null($standingEntry)) {
                continue;
            }
            $image = $this->getAvatarImage($standingEntry);
            $participantData = $this->getParticipantResults($group, $standingEntry);
            $values[] = [
                'position' => $position,
                'logo' => $image,
                'name' => $standingEntry->getName(),
                'games' => $participantData['games'],
                'wins' => $participantData['wins'],
                'draws' => $participantData['draws'],
                'losses' => $participantData['losses'],
                'points' => $participantData['points'],
                'participantID' => $standingEntry->getID()
            ];
            $position++;
        }

        usort($values, function ($item1, $item2) {
            return $item2['points'] <=> $item1['points'];
        });

        $rank = 1;
        foreach($values as $key => $standing_entry) {
            $values[$key]['position'] = $rank;
            $rank++;
        }

        return $values;
    }

    private function getParticipantResults($group, $participant) {
        $encounters = $group->getEncounters();
        $data = [
            'games' => 0,
            'wins' => 0,
            'losses' => 0,
            'draws' => 0,
            'points' => 0,
        ];
        foreach($encounters as $encounter) {
            $encounter_slots = $encounter->getSlotAssignment();
            $encounter_slots = $encounter_slots->getSlots();
            if($encounter_slots[0]->getID() == $participant->getID() || $encounter_slots[1]->getID() == $participant->getID()) {
                // is encounter of this participant;
                $storage = DatasetStorage::getInstance('encounter_result', $encounter->getItem()->getID());
                $dataset = $storage->loadAll();

                // participant is encounter a or b?
                if($encounter_slots[0]->getID() == $participant->getID()) {
                    $a_or_b = 'a';
                } else {
                    $a_or_b = 'b';
                }

                $systemSet = $dataset->getDataSegment('system');
                $adminSet = $dataset->getDataSegment('admin');
                if(! is_null($systemSet) || ! is_null($adminSet)) {
                    $data['games']++;
                }

                if(! is_null($systemSet) && is_null($adminSet)) {
                    $result_a = $systemSet->getValue('points_a', 'system');
                    $result_b = $systemSet->getValue('points_b', 'system');
                    $data = $this->calculatePointsWinsAndLosses($result_a, $result_b, $a_or_b, $data);
                }
                
                if(! is_null($adminSet)) {
                    $result_a = $adminSet->getValue('points_a', 'admin');
                    $result_b = $adminSet->getValue('points_b', 'admin');
                    $data = $this->calculatePointsWinsAndLosses($result_a, $result_b, $a_or_b, $data);
                }
            }
        }
        return $data;
    }

    private function calculatePointsWinsAndLosses($result_a, $result_b, $a_or_b, $data) {
        if($a_or_b == 'a' && $result_a > $result_b) {
            $data['points']+=3;
            $data['wins']++;
        }
        if($a_or_b == 'a' && $result_a < $result_b) {
            $data['losses']++;
        }

        if($a_or_b == 'b' && $result_b > $result_a) {
            $data['points'] = $data['points']+3;
            $data['wins']++;
        }
        if($a_or_b == 'b' && $result_b < $result_a) {
            $data['losses']++;
        }

        // game was a draw...
        if($result_a == $result_b) {
            $data['draws']++;
            $data['points']++;
        }
        return $data;
    }

}