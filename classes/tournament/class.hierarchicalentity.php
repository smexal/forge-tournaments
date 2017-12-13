<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
use \Forge\Core\Classes\Relations\Enums\Prepares;

abstract class HierarchicalEntity {
    protected $item;
    protected $participant_list; // ParticipantList

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

    public function getParticipantList() {
        if(!is_null($this->participant_list)) {
            return $this->participant_list;
        }

       return $this->participant_list = $this->loadParticipantList();
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

    protected function saveParticipantList() {    
        $participant_ids = $this->getParticipantList()->getParticipants();
        $this->getItem()->setMeta('ft_participant_list', $participant_ids);
    }
    
    protected function loadParticipantList() {
        // Load Participant list form DB
        $size = $this->getItem()->getMeta('ft_participant_list_size');
        $size = $size ? $size : 1;
        
        $participants = $this->getMeta('ft_participant_list', []);
        return new ParticipantList($size, $participants);
    }

    public function addParticipant($participant) {
        $participant = is_object($participant) ? $participant->getID() : $participant;
        $this->getParticipantList()->addParticipant($participant);
        $this->saveParticipantList();
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

    protected function getMeta($key, $default=null, $lang=false) {
        $value = $this->getItem()->getMeta($key, $lang);

        if($value === false) {
            return $default;
        }
        return $value;
    }

}
