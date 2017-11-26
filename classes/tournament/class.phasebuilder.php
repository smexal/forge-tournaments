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

            case PahseTypes::REGISTRATION:
                $this->buildRegistrationPhase($phase);
            break;

            case PahseTypes::GROUP:
                $this->buildGroupPhase($phase);
            break;

            case PahseTypes::KOSYSTEM:
                $this->buildBracketPhase($phase);
            break;

            case PahseTypes::PERFORMANCE:
                $this->buildPerformancePhase($phase);
            break;
        }

    }
    
    private function buildRegistrationPhase($phase) {

    }
    
    private function buildGroupPhase($phase) {
        $scoring = $phase->getScoring();
        $num_participants = $phase->getParticipantList()->count();
        $group_size = $phase->getGroupSize();
        $num_groups = ceil($num_participants / $group_size);
        
        $tree = new CollectionTree($phase->getItem());
        $group_list = $this->buildGroups($num_groups, $group_size);
    }
    
    private function buildBracketPhase($phase) {

    }
    
    private function buildPerformancePhase($phase) {

    }

    /********************
     * BUILD SUB NODES
     ********************/
    private function buildGroups($num, $size) {
        $args = [
            'name' => \i('Group IDX %d'),
            'type' => GroupCollection::COLLECTION_NAME
        ];
        $metas = [
            'ft_group_nr' =>[
                'value' => 'TBD',
                'lang' => '0',
            ],
            'ft_group_size' =>[
                'value' => $size,
                'lang' => '0',
            ]
        ];
        $groups = [];
        for($i = 0; $i < $num; $i++) {
            $metas['ft_group_nr']['value'] = $i + 1;
            $item = new CollectionItem(CollectionItem::create($args, $metas));
            PoolRegistry::getPool('collection')->setInstance($item->id, $item);
            
            $group = PoolRegistry::getPool('group')->getInstance($item->id, $item);

            $groups[] = $group;
        }

    }

}
