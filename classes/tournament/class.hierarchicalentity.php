<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;

abstract class HierarchicalEntity {
    protected $item;
    protected $participant_list; // ParticipantList

    /**
     * @param mixed $item The Related CollectionItem
     */
    public function __construct($item) {
        $this->item = $item;
    }

    public function getParticipantList() {
        if(!is_null($this->participant_list)) {
            return $this->participant_list;
        }
        $this->participant_list = new ParticipantList();
        // Load Participant list form DB
    }

    public function getItem() {
        return $this->item;
    }

    public function getID() {
        return $this->getItem()->getID();
    }

    public function addParticipant($participant) {
      $this->partcipant_list->addParticipant($participant);
    }

    public function setParent(HierarchicalEntity $entity) {
        $this->getItem()->setParent($entity->getItem()->getID());
    }

}
