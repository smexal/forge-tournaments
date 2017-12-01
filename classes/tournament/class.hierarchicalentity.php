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

    protected function loadParticipantList() {
         // Load Participant list form DB
        $size = $this->getItem()->getMeta('ft_participant_list_size');
        $size = $size ? $size : 1;
        var_dump($size);die();
        $relation = App::instance()->rd->getRelation('ft_participant_list');

        $participants = $relation->getOfLeft($this->getItem()->getID(), Prepares::AS_IDS_RIGHT);
        
        return new ParticipantList($size, $participants);
    }

    public function addParticipant($participant) {
        $this->getParticipantList()->addParticipant($participant->getID());
    }

    public function setParent(HierarchicalEntity $entity) {
        $this->getItem()->setParent($entity->getItem()->getID());
    }

    protected function getMeta($key, $default=null, $lang=false) {
        $value = $this->getItem()->getMeta($key, $lang);

        if($value === false) {
            return $default;
        }
        return $value;
    }

}
