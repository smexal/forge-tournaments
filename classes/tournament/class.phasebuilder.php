<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Traits\Singleton;
use Forge\Modules\ForgeTournaments\PhaseTypes;
use Forge\Core\Classes\CollectionItem;

use Forge\Modules\ForgeTournaments\Calculations\Nodes\CollectionNode;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators\BreadthFirstIterator;

use Forge\Modules\ForgeTournaments\Data\StorageNodeFactory;

class PhaseBuilder {
    use Singleton;

    protected function __construct() {
        \registerModifier(FORGE_TOURNAMENT_NS . '/phase/canChangeState', [$this, 'canChangeState']);
        \registerEvent(FORGE_TOURNAMENT_NS . '/phase/changedStatus', [$this, 'onPhaseStateChange']);
    }

    // TODO: Add validation if enough participants are available
    // If the groups can be filled usw.
    public function canChangeState($dochange, $phase, $new_state, $old_state) {
        return true;
    }

    public function onPhaseStateChange($phase, $new_state, $old_state) {
        switch ($new_state) {
            case PhaseState::READY:
                if(!$this->canBuild()) {
                    return false;
                }
                $this->clean($phase);
                $this->build($phase);
            break;
            
            default:
                # code...
            break;
        }
        return true;
    }

    /**
     * Remove old groups, matches and encounters
     */
    public function clean($phase) {
        $tree = new CollectionTree($phase->getItem());
        $tree->build();

        $iterator = new BreadthFirstIterator($tree->getRoot());
        while(!is_null($n = $iterator->nextNode())) {
            $item = $node->getItem();
            $item->delete();
            $storage_node = StorageNodeFactory::getByCollectionID($item->getID());
            $storage_node->deleteAllData();
        }

    }

    /********************
     * BUILD PHASE
     ********************/
    public function build($phase) {
        $this->buildPhase($phase);
        switch ($phase->getType()) {

            case PhaseTypes::REGISTRATION:
                $this->buildRegistrationPhase($phase);
            break;

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
        $phase->getItem()->setMeta('data_schema', $scoring['phase']);
    }
    
   public function buildRegistrationPhase($phase) {

    }
    
   public function buildGroupPhase($phase) {
        $scoring = $phase->getScoringConfig();
        $schema = $phase->getScoringSchemas();
        $num_participants = $phase->getParticipantList()->count();
        $group_size = $phase->getGroupSize();
        $num_groups = ceil($num_participants / $group_size);

        $num_remaining = $num_participants % $group_size;
        var_dump(
            "encounter_handling: " . $scoring['encounter_handling'] . "\n" .
            "schema: " . $schema['group'] . "\n" .
            "num_participants: " . $num_participants . "\n" .
            "group_size: " . $group_size . "\n" .
            "num_groups: " . $num_groups . "\n" .
            "num_remaining: " . $num_remaining
        );

        $slot_start = 0;
        $slot_end = 0;
        $groups = $this->buildGroups($phase->getID(), $schema['group'], $num_groups, $group_size);
        foreach($groups as $idx => $group) {
            // Distribute missing slots to the remaining groups
            $num_group_participants = $group_size + (($num_groups - $idx) <= $num_remaining ? -1 : 0);
            $slot_end = $slot_start + $num_group_participants - 1;
            /// The following is for when 2 participants have an encounter
            if($scoring['encounter_handling'] == ScoringDefinitions::ENCOUNTER_HANDLING_VERSUS) {
                // Remove 1 because a participant cannot play against himself
                $n = $num_group_participants - 1;
                // Gaussian sum formula
                $num_encounters = $n * ($n + 1) / 2;
                $encounters = $this->buildEncounters($group->getID(), $schema['encounter'], $num_encounters);
                
                $this->recursiveAssign($encounters, range($slot_start, $slot_end));
                $slot_start = $slot_end + 1;
            } else if ($scoring['encounter'] == ScoringDefinitions::ENCOUNTER_HANDLING_SINGLE) {
                $slot_start = $idx * $group_size;
                for($i = 0; $i < count($encounters); $i++) {
                    $encounter = $encounters[$i];
                    $encounter->setSlots([$slot_start + $i]);
                }
            }
        }
    }
    
   public function buildBracketPhase($phase) {

    }
    
   public function buildPerformancePhase($phase) {

    }

    public function recursiveAssign(&$encounters, $slot_ids) {
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
            $encounter->setSlots([$first_slot, $second_slot]);
        }
        $this->recursiveAssign($encounters, $slot_ids);
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

            $args['name'] = sprintf($args['name'], chr(64 + $metas['ft_group_nr']['value']));
            $item = new CollectionItem(CollectionItem::create($args, $metas));
            PoolRegistry::instance()->getPool('collection')->setInstance($item->getID(), $item);
            
            $group = PoolRegistry::instance()->getPool('group')->getInstance($item->getID(), $item);
            $groups[] = $group;
        }

        return $groups;
    }

    public function buildEncounters($parent_id, $data_schema, $num) {
        $args = [
            'name' => \i('Encounter %d'),
            'type' => GroupCollection::COLLECTION_NAME,
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
        ];

        $encounters = [];
        for($i = 0; $i < $num; $i++) {
            $metas['ft_encounter_nr']['value'] = $i + 1;

            $args['name'] = sprintf($args['name'], $metas['ft_encounter_nr']['value']);

            $item = new CollectionItem(CollectionItem::create($args, $metas));
            PoolRegistry::instance()->getPool('collection')->setInstance($item->getID(), $item);
            
            $encounter = PoolRegistry::instance()->getPool('encounter')->getInstance($item->getID(), $item);
            $encounters[] = $encounter;
        }

        return $encounters;
    }

}
