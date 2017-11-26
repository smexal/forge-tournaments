<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
use Forge\Modules\ForgeTournaments\Utils;
use Forge\Modules\ForgeTournaments\Scoring\ScoringProvider;

class Phase {
    protected $collection_id;
    protected $collection;
    protected $participant_list; // ParticipantList

    /**
     * @param mixed $item Either the item itself or the item_id
     */
    public function __construct($item) {
        $this->item = $item;
    }

    public function getParticipantList() {
        if(!is_null($this->participant_list)) {
            return $this->participant_list;
        }
        $this->participant_list = new ParticipantList();
    }

    public function getItem() {
        return $this->item;
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

        $this->getItem()->setMeta('ft_phase_status', $new_status);
        \fireEvent(FORGE_TOURNAMENT_NS . '/phase/changeStatus', $this, $new_status, $status);

        return true;
    }

    public function getType() {
        return $this->getItem()->getMeta('ft_phase_type');
    }

    public function getGroupSize() {
        return $this->getItem()->getMeta('ft_group_size');
    }

    public function getScoring() {
        $scoring_type = $this->getItem()->getMeta('ft_scoring');
        return ScoringProvider::instance()->getScoring($scoring);
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
