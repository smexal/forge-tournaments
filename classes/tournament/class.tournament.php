<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
use Forge\Modules\ForgeTournaments\Utils;
use Forge\Modules\ForgeTournaments\Scoring\ScoringProvider;

class Tournament extends HierarchicalEntity {
    protected $item;
    protected $participant_list; // SlotAssignment

    /**
     * @param mixed $item The Related CollectionItem
     */
    public function __construct($item) {
        $this->item = $item;
    }

    public function getPhases() {
        $children = $this->getChildren();
        foreach($children as $key => $child) {
            $children[$key] = PoolRegistry::instance()->getPool('phase')->getInstance($child->getID(), $child);
        }
        return $children;
    }

    // The slot assignment is not done on the tournment level
    public function getSlotAssignment() {}
    protected function getSlotAssignmentData() {
        return null;
    }
    protected function saveSlotAssignment() {}
    protected function loadSlotAssignment() {}
    public function setNumSlots($slot_num=2) {}
    public function addParticipant($participant) {}
    public function setParent(HierarchicalEntity $entity) {}
    public function getParent() {
        return null;
    }

}
