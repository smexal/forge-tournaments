<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Relations\Enums\Prepares;

abstract class HierarchicalEntity {
    protected $item;
    protected $slot_assignment; // SlotAssignment

    /**
     * @param mixed $item The Related CollectionItem
     */
    public function __construct($item) {
        $this->item = $item;
    }

    public function getItem() {
        return $this->item;
    }

    public function getID() {
        return $this->getItem()->getID();
    }

    public function getName() {
        return $this->getItem()->getName();
    }

    public function getType() {
        return $this->getItem()->getType();
    }

    public function getSlotAssignment() {
        if(!is_null($this->slot_assignment)) {
            return $this->slot_assignment;
        }

       return $this->slot_assignment = $this->loadSlotAssignment();
    }

    protected function getTournament() {
        $parent = $this->getItem();
        if($parent === TournamentCollection::COLLECTION_NAME) {
            return $this;
        }
        while($parent) {
            $parent = $this->getParent();
            if($parent->getType() === TournamentCollection::COLLECTION_NAME) {
                return $parent;
            }
        }
        throw new \Exception("Can not find root entity");
    }

    protected function saveSlotAssignment() {
        $participant_ids = $this->getSlotAssignment()->getSlotData();
        $this->getItem()->updateMeta('ft_slot_assignment', json_encode($participant_ids), false);
    }
    
    protected function loadSlotAssignment() {
        // Load Participant list form DB
        $data = $this->getSlotAssignmentData();
        $p_data = new ParticipantSlotAssignment(count($data), $data);
        return $p_data;
    }

    protected function getSlotAssignmentData() {
        $data = $this->getItem()->getMeta('ft_slot_assignment');
        if(!$data) {
            return [];
        }
        $data = (array) $data;
        return $data;
    }

    public function setNumSlots($slot_num=2) {
        $this->getSlotAssignment()->setNumSlots($slot_num);
    }

    public function addParticipant($participant) {
        $this->getSlotAssignment()->addEntry($participant);
        $this->saveSlotAssignment();
    }

    public function setParent(HierarchicalEntity $entity) {
        $this->getItem()->setParent($entity->getItem()->getID());
    }

    public function getParent() {
        return $this->getItem()->getParent();
    }

    public function getChildren() {
        return $this->getItem()->getChildren();
    }

    public function getMeta($key, $default=null, $lang=false) {
        $value = $this->getItem()->getMeta($key, $lang);
        if($value === false) {
            return $default;
        }
        return $value;
    }

}
