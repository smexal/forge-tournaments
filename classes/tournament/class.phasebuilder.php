<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Core\Traits\Singleton;
use Forge\Modules\ForgeTournaments\PhaseTypes;

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
        $scoring = $phase->getScoring();
        $phase->getItem()->setMeta('data_schema', $scoring['phase']);
    }
    
   public function buildRegistrationPhase($phase) {

    }
    
   public function buildGroupPhase($phase) {
        $scoring = $phase->getScoring();
        $scoring = $scoring['group'];
        $num_participants = $phase->getParticipantList()->count();
        $group_size = $phase->getGroupSize();
        $num_groups = ceil($num_participants / $group_size);

        $num_remaining = $num_participants % $group_size;

        $tree = new CollectionTree($phase->getItem());
        $groups = $this->buildGroups($phase->getID(), $scoring['group'], $num_groups, $group_size);

        foreach($groups as $idx => $group) {
            // Distribute missing slots to the remaining groups
            $num_encounters = $group_size - ($idx >= $num_remaining ? -1 : 0);
            // Gaussian sum formula
            $num = $num_encounters * ($num_encounters + 1) / 2;
            $encounters = $this->buildEncounters($group->getID(), $scoring['encounter'], $num);
        }
    }
    
   public function buildBracketPhase($phase) {

    }
    
   public function buildPerformancePhase($phase) {

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
            'ft_group_nr' =>[
                'value' => 'TBD',
                'lang' => '0',
            ],
            'ft_group_size' =>[
                'value' => $size,
                'lang' => '0',
            ],
            'ft_data_schema' =>[
                'value' => $data_schema,
                'lang' => '0',
            ],
        ];
        
        $groups = [];
        for($i = 0; $i < $num; $i++) {
            $metas['ft_group_nr']['value'] = $i + 1;

            $args['name'] = sprintf($args['name'], chr(64 + $metas['ft_group_nr']['value']));

            $item = new CollectionItem(CollectionItem::create($args, $metas));
            PoolRegistry::getPool('collection')->setInstance($item->id, $item);
            
            $group = PoolRegistry::getPool('group')->getInstance($item->id, $item);
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
            PoolRegistry::getPool('collection')->setInstance($item->id, $item);
            
            $encounter = PoolRegistry::getPool('encounter')->getInstance($item->id, $item);
            $encounters[] = $encounter;
        }

        return $encounters;
    }

}
