<?php

namespace Forge\Modules\ForgeTournaments;

use Forge\Modules\ForgeTournaments\Calculations\Nodes\CollectionNode;
use Forge\Modules\ForgeTournaments\Calculations\Nodes\Iterators\BreadthFirstIterator;
use Forge\Core\Traits\Singleton;
use Forge\Modules\ForgeTournaments\PhaseTypes;

class PhaseBuilder {
    use Singleton;

    protected function __construct() {
        \registerEvent(FORGE_TOURNAMENT_NS . '/phase/changeStatus', [$this, 'onPhaseStateChange']);
    }

    public function onPhaseStateChange($phase, $new_state, $old_state) {
        switch ($new_state) {
            case PhaseState::READY:
                $this->clean($phase);
                $this->build($phase);
            break;
            
            default:
                # code...
            break;
        }
    }

    public function clean($phase) {
        $tree = new CollectionTree($phase->getItem());
        $tree->build();

        $iterator = new BreadthFirstIterator($tree->getRoot());
        $list = [];
        while(!is_null($n = $iterator->nextNode())) {
            $item = $node->getItem();
            $item->delete();
        }

    }

    /********************
     * BUILD PHASE
     ********************/
    public function build($phase) {
        switch ($phase->getType()) {

            case PhaseTypes::REGISTRATION:
                $this->buildRegistrationPhase($phase);
            break;

            case PhaseTypes::GROUP:
                $this->buildPhase($phase);
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

   public function buildPhase($scoring) {
        $scoring = $phase->getScoring();
        
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
            // Distribute missing slots to the remaining 
            $num_encounters = $group_size - ($idx >= $num_remaining ? -1 : 0);
            // Summenformel
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
            'name' => \i('Group IDX %d'),
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

            $args['name'] = sprintf($args['name'], $metas['ft_group_nr']['value']);

            $item = new CollectionItem(CollectionItem::create($args, $metas));
            PoolRegistry::getPool('collection')->setInstance($item->id, $item);
            
            $group = PoolRegistry::getPool('group')->getInstance($item->id, $item);
            $groups[] = $group;
        }

        return $groups;
    }

    public function buildEncounters($parent_id, $data_schema, $num) {
        $args = [
            'name' => \i('Encounter IDX %d'),
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
            $item->setMeta('ft_data_schema');
            PoolRegistry::getPool('collection')->setInstance($item->id, $item);
            
            $encounter = PoolRegistry::getPool('encounter')->getInstance($item->id, $item);
            $encounters[] = $encounter;
        }

        return $encounters;
    }

}
