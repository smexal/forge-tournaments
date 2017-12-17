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

    public function changeStatus($new_status) {
        $new_status = (int) $new_status;
        $status = (int) $this->getItem()->getMeta('ft_phase_status');
        if(!array_key_exists($new_status, Utils::getPhaseStates())) {
            return false;
        }

        $can_change = \triggerModifier(FORGE_TOURNAMENT_NS . '/phase/canChangeState', true, $this, $new_status, $status);
        error_log("can change: " . $can_change ? 'Y': 'N');
        if(!$can_change && $status != $new_status) {
            return false;
        }

        $this->getItem()->setMeta('ft_phase_status', $new_status);
        \fireEvent(FORGE_TOURNAMENT_NS . '/phase/changedStatus', $this, $new_status, $status);

        return true;
    }

    public function getType() {
        return $this->getItem()->getMeta('ft_phase_type');
    }

    public function getGroupSize() {
        return $this->getMeta('ft_group_size', 4);
    }

    public function getScoring() {
        $scoring_type = $this->getItem()->getMeta('ft_scoring');
        return ScoringProvider::instance()->getScoring($scoring_type);
    }
    
    public function getScoringConfig() {
        return $this->getScoring()['config'];
    }

    public function getScoringSchemas() {
        return $this->getScoringConfig()['phase_types'][$this->getType()]['schemas'];
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
        return ceil($this->getSlotAssignment()->numSlots() / $this->getGroupSize());
    }

  /*
    fn setPreviousPhase
    fn setNextPhase
    fn validate // Check if completed
    fn shuffleSlotAssignment(Random/BestScore/)
    fn generateEncounters
    fn close
    fn set/get/Members
    fn render(Admin/User/Preview/SmallAdmin usw.)
  */

}
