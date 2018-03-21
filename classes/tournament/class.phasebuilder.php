<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Traits\Singleton;
use Forge\Modules\ForgeTournaments\PhaseTypes;
use Forge\Core\Classes\CollectionItem;

use Forge\Modules\ForgeTournaments\Calculations\CollectionTree;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\CollectionNode;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators\BreadthFirstIterator;

use Forge\Modules\ForgeTournaments\Data\StorageNodeFactory;

class PhaseBuilder {
    use Singleton;

    protected function __construct() {
        \registerModifier(FORGE_TOURNAMENT_NS . '/phase/canChangeState', [$this, 'canChangeState']);
        \registerEvent(FORGE_TOURNAMENT_NS . '/phase/changedState', [$this, 'onPhaseStateChange']);
    }

    // TODO: Add validation if enough participants are available
    // If the groups can be filled usw.
    public function canChangeState($dochange, $phase, $new_state, $old_state) {
        return true;
    }

    /**
     * Is only called if the previous state is NOT the same.
     * A phase state can be changed back to the previous state
     */
    public function onPhaseStateChange($phase, $new_state, $old_state) {
        error_log("$old_state to -> $new_state");
        // Upon entering from previous state. E.G CONFIG_PHASETYPE --to--> REGISTRATION
        if($old_state < $new_state) {
            switch ($new_state) {
                case PhaseState::READY:
                    error_log("DO THE BUILDING !");
                    $this->clean($phase);
                    $this->build($phase);
                break;

                default:
                    # code...
                break;
            }
        // Upon returning from next state. E.G REGISTRATION --back--> CONFIG_PHASETYPE
        } else if ($old_state > $new_state) {

        }
        return true;
    }

    /**
     * Remove old groups, matches and encounters
     */
    public function clean($phase) {
        error_log("CLEANING PHASE {$phase->getID()}");
        $tree = new CollectionTree($phase->getItem());
        $tree->build();

        $iterator = new BreadthFirstIterator($tree->getRoot());
        while(!is_null($n = $iterator->nextNode())) {
            $item = $n->getItem();
            $storage_node = StorageNodeFactory::getByCollectionID($item->getID());
            if(!is_null($storage_node)) {
                $storage_node->deleteAllData();
            }
            // Only delete children
            if($phase->getItem()->getID() != $item->getID()) {
                $item->delete();
            }
        }
    }

    /********************
     * BUILD PHASE
     ********************/
    public function build($phase) {
        error_log("BUILD PHASE {$phase->getID()}");
        $this->buildPhase($phase);
        switch ($phase->getPhaseType()) {

            case PhaseTypes::GROUP:
                $this->buildGroupPhase($phase);
            break;

            case PhaseTypes::KOSYSTEM:
                $this->buildBracketPhase($phase);
            break;

            case PhaseTypes::PERFORMANCE:
                $this->buildPerformancePhase($phase);
            break;
        }
    }

   public function buildPhase($phase) {
        $scoring = $phase->getScoringSchemas();
        $phase->getItem()->setMeta('ft_data_schema', $scoring['phase']);
    }
    
   public function buildGroupPhase($phase) {
        error_log("BUILD GROUP PHASE {$phase->getID()}");
        $scoring = $phase->getScoringConfig();
        $schema = $phase->getScoringSchemas();
        $participants = $phase->getSlotAssignment();

        $num_participants = $participants->count();

        $group_size = $phase->getGroupSize();
        $num_groups = ceil($num_participants / $group_size);

        $num_remaining = $num_participants % $group_size;

        $slot_start = 0;
        $slot_end = 0;
        $groups = $this->buildGroups($phase->getID(), $schema['group'], $num_groups, $group_size);

        foreach($groups as $idx => $group) {
            // Distribute missing slots to the remaining groups
            $num_group_participants = $group_size + (($num_groups - $idx) <= $num_remaining ? -1 : 0);
            $slot_end = $slot_start + $num_group_participants - 1;
            // The following is for when 2 participants have an encounter
            if($scoring['encounter_handling'] == ScoringDefinitions::ENCOUNTER_HANDLING_VERSUS) {
                $slot_range = range($slot_start, $slot_end);

                // Remove 1 because a participant cannot play against himself
                $n = $num_group_participants - 1;
                // Gaussian sum formula
                $num_encounters = $n * ($n + 1) / 2;
                $encounters = $this->buildEncounters($group->getID(), $schema['encounter'], $num_encounters, 2);
                $this->recursiveAssign($participants, $encounters, $slot_range);
                
                $group->setNumSlots($group_size);
                foreach($slot_range as $slot_idx) {
                    $participant = $participants->getSlot($slot_idx);
                    if(is_null($participant)) {
                        continue;
                    }
                    $group->addParticipant($participant);
                }

                $slot_start = $slot_end + 1;
            // This is if the encounter is a "performance competition"
            } else if ($scoring['encounter'] == ScoringDefinitions::ENCOUNTER_HANDLING_SINGLE) {
               throw new \Exception("Not yet defined!");
               /* $slot_start = $idx * $group_size;
                for($i = 0; $i < count($encounters); $i++) {
                    $encounter = $encounters[$i];
                    $encounter->setNumSlots(1);
                    $encounter->setSlots([$slot_start + $i]);
                }*/
            }
        }
    }

    // Check out: https://www.printyourbrackets.com/double-elimination-tournament-brackets.html
    public function buildBracketPhase($phase) {
        error_log("BUILD BRACKET PHASE {$phase->getID()}");
        $scoring = $phase->getScoringConfig();
        $schema = $phase->getScoringSchemas();
        $participants = $phase->getSlotAssignment();
        $num_participants = $participants->count();

        $bracketSize = $this->getNextPowerOf2($num_participants) / 2;

        // The looser bracket has twice the amount of encounters because on
        // each odd encounter section the loosers from the winner bracket enter
        // the fray
        // single elimination
        $num_encounters = 0;
        $bracketSizeForCalc = $bracketSize;
        while($bracketSizeForCalc > 0) {
            $num_encounters += $bracketSizeForCalc;
            $bracketSizeForCalc = $bracketSizeForCalc / 2;
        }
        error_log("SINGLE >>> " . $num_encounters);
        

        // double eliminnation
        $bracketSizeForCalc = $bracketSize;
        $doubleSwitch = true;
        while($bracketSizeForCalc > 0) {
            if($doubleSwitch) {
                $bracketSizeForCalc = $bracketSizeForCalc / 2;
            }
            $doubleSwitch = ! $doubleSwitch;
            $num_encounters += $bracketSizeForCalc;
        }
        error_log("DOUBLE >>> " . $num_encounters);

        $group = $this->buildGroups($phase->getID(), $schema['group'], 1, $num_encounters)[0];
        $encounters = $this->buildEncounters($group->getID(), $schema['encounter'], $num_encounters, 2);
        
        $this->bracketAssign($participants, $encounters, $bracketSize);
    }

    private function bracketAssign($participants, &$encounters, $bracketSize) {
        $encounter_no = 0;
        $bracketCounter = 0;
        // set the first encounter participant
        foreach($encounters as $encounter) {
            $encounter->setNumSlots(2);
            $encounter->addParticipant($participants->getSlot($encounter_no));
            $encounter_no++;
            $bracketCounter++;
            if($bracketCounter == $bracketSize) {
                break;
            }
        }

        // loop a second time to set second participants to encounter
        foreach($encounters as $encounter) {
            $participantToAdd = $participants->getSlot($encounter_no);
            $encounter->addParticipant($participantToAdd);
            $encounter_no++;
        }
    }
    
    public function getNextPowerOf2($number) {
        $exp = log($number, 2);
        $exp = ceil($exp);
        return pow(2, $exp);
    }

    public function getNumNodesOfBtree($depth) {
        return pow($depth, 2) - 1;
    }

    public function getDepthOfNNodes($number) {
        return log($number + 1, 2);
    }

    public function buildPerformancePhase($phase) {

    }

    public function recursiveAssign($participants, &$encounters, $slot_ids) {
        if(count($slot_ids) <= 1) {
            return;
        }
        // Remove the first slot
        // A B C
        // B C
        $first_slot = array_shift($slot_ids);
        foreach($slot_ids as $second_slot) {
            if(count($encounters) == 0) {
                return;
            }
            $encounter = array_shift($encounters);
            $encounter->setNumSlots(2);
            $encounter->addParticipant($participants->getSlot($first_slot));
            $encounter->addParticipant($participants->getSlot($second_slot));
        }
        $this->recursiveAssign($participants, $encounters, $slot_ids);
    }

    /********************
     * BUILD SUB NODES
     ********************/
   public function buildGroups($parent_id, $data_schema, $num, $size) {
        $args = [
            'name' => \i('Group %s'),
            'type' => GroupCollection::COLLECTION_NAME,
            'parent' => $parent_id
        ];
        $metas = [
            'ft_group_nr' => [
                'value' => 'TBD',
            ],
            'ft_group_size' => [
                'value' => $size,
            ],
            'ft_data_schema' => [
                'value' => $data_schema,
            ],
            'ft_participant_list_size' => [
                'value' => $size
            ] 
        ];

        $groups = [];
        for($i = 0; $i < $num; $i++) {
            $metas['ft_group_nr']['value'] = $i + 1;

            $args['name'] = sprintf( \i('Group %s'), chr(64 + $metas['ft_group_nr']['value']));
            $item = new CollectionItem(CollectionItem::create($args, $metas));
            PoolRegistry::instance()->getPool('collectionitem')->setInstance($item->getID(), $item);
            
            $group = PoolRegistry::instance()->getPool('group')->getInstance($item->getID(), $item);
            $groups[] = $group;
        }
        return $groups;
    }

    public function buildEncounters($parent_id, $data_schema, $num, $size=2) {
        $args = [
            'name' => \i('Encounter %d'),
            'type' => EncounterCollection::COLLECTION_NAME,
            'parent' => $parent_id
        ];
        $metas = [
            'ft_encounter_nr' =>[
                'value' => 'TBD',
                'lang' => '0',
            ],
            'ft_data_schema' =>[
                'value' => $data_schema,
                'lang' => '0',
            ],
            'ft_participant_list_size' => [
                'value' => $size
            ] 
        ];

        $encounters = [];
        for($i = 0; $i < $num; $i++) {
            $metas['ft_encounter_nr']['value'] = $i + 1;

            $args['name'] = sprintf(\i('Encounter %d'), $metas['ft_encounter_nr']['value']);

            $item = new CollectionItem(CollectionItem::create($args, $metas));
            PoolRegistry::instance()->getPool('collectionitem')->setInstance($item->getID(), $item);
            
            $encounter = PoolRegistry::instance()->getPool('encounter')->getInstance($item->getID(), $item);
            $encounters[] = $encounter;
        }

        return $encounters;
    }

    public function buildBTree($current, $encounters, $participants=[], $depth=0, $max_depth=0, $is_looser_bracket=false) {
        if(is_null($current)) {
            return;
        }
        $node = [
            'node' => $current,
            'children' => []
        ];
        $current_id = $current->getID();
        if(!is_null($left = array_pop($encounters))) {
            $left->updateMeta('ft_winner_encounter', $current_id);
            $node['children'][] = $this->buildBTree($left, $encounters);

        }
        if(!is_null($right = array_pop($encounters))) {
            $right->updateMeta('ft_winner_encounter', $current_id);
            $node['children'][] = $this->buildBTree($right, $encounters);
        }
        return $node;
    }

}
