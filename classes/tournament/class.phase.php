<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;

class Phase {
    protected $collection;
    protected $participant_list; // ParticipantList


    public function __construct($collection_id) {
      $this->participant_list = new ParticipantList();
    }

    public function addParticipant($participant) {
      $this->partcipant_list->addParticipant($participant);
    }
  /*
    fn changeState
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
