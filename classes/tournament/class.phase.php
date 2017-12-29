<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
use Forge\Modules\ForgeTournaments\Utils;
use Forge\Modules\ForgeTournaments\Scoring\ScoringProvider;

class Phase extends HierarchicalEntity {
    protected $item;
    protected $participant_list; // SlotAssignment

    /**
     * @param mixed $item The Related CollectionItem
     */
    public function __construct($item) {
        $this->item = $item;
    }

    public function changeState($new_state) {
        $new_state = (int) $new_state;
        $state = $this->getState();
        
        if(!array_key_exists($new_state, Utils::getPhaseStates())) {
            return false;
        }

        $can_change = \triggerModifier(FORGE_TOURNAMENT_NS . '/phase/canChangeState', true, $this, $new_state, $state);
        
        if(!$can_change && $state != $new_state) {
            return false;
        }

        $this->getItem()->updateMeta('ft_phase_state', $new_state, false);
        \fireEvent(FORGE_TOURNAMENT_NS . '/phase/changedState', $this, $new_state, $state);

        return true;
    }


    public function getPhaseType() {
        return $this->getItem()->getMeta('ft_phase_type');
    }

    public function getState() {
        return (int) $this->getItem()->getMeta('ft_phase_state');
    }

    public function getGroupSize() {
        $data =  $this->getMeta('ft_group_size', 4);
        return $data;
    }

    public function getScoring() {
        $scoring_type = $this->getItem()->getMeta('ft_scoring');
        return ScoringProvider::instance()->getScoring($scoring_type);
    }
    
    public function getScoringConfig() {
        return $this->getScoring()['config'];
    }

    public function getScoringSchemas() {
        return $this->getScoringConfig()['phase_types'][$this->getPhaseType()]['schemas'];
    }

    public function addGroups($groups) {
        foreach($group as $group) {
            $group->setParent($this);
        }
    }

    public function getGroups() {
        $children = $this->getChildren();
        foreach($children as $key => $child) {
            $children[$key] = PoolRegistry::instance()->getPool('group')->getInstance($child->getID(), $child);
        }
        return $children;
    }

    public function getGroupCount() {
        $num_slots = $this->getSlotAssignment()->numSlots();
        $group_size = $this->getGroupSize();
        $group_count = ceil ($num_slots / $group_size);
        return $group_count;
    }

}
