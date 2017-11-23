<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
use Forge\Modules\ForgeTournaments\Utils;

class Phase {
    protected $collection_id;
    protected $collection;
    protected $participant_list; // ParticipantList


    public function __construct($collection_id) {
        $this->item_id;
        $this->participant_list;
    }

    public function getParticipantList() {
        if(!is_null($this->participant_list)) {
            return $this->participant_list;
        }
        $this->participant_list = new ParticipantList();
    }

    public function getItem() {
        if(!is_null($this->item)) {
            return $this->item;
        }
        $this->item = new CollectionItem($this->item_id);
        return $this->item;
    }

    public function getItemID() {
        return $this->item_id;
    }

    public function addParticipant($participant) {
      $this->partcipant_list->addParticipant($participant);
    }

    public function changeStatus($new_status) {
        $status = $this->getItem()->getMeta('ft_phase_status');
        if(!in_array($new_status, Utils::getPhaseStates())) {
            return false;
        }

        if($status != $new_status) {
            return false;
        }

        \fireEvent(FORGE_TOURNAMENT_NS . '/phase/changeStatus', $new_status, $status);
        $this->getItem()->setMeta('ft_phase_status', $new_status);

        return true;
    }
  /*
    fn setPreviousPhase
    fn setNextPhase
    fn validate // Check if completed
    fn addParticipant // Assignment method used in shufflePool
    fn addParticipants
    fn shuffleParticipantList(Random/BestScore/)
    fn generateEncounters
    fn close
    fn set/get/Members
    fn render(Admin/User/Preview/SmallAdmin usw.)
  */

}
