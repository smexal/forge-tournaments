<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
/**
 * The Pool contains a number of participants.
 * Each participant has a slot place
 * The slot place can be reassigned by shuffling
 */
class ParticipantList {
    protected $participants;
    protected $num_slots;
    protected $seeder;

    public function __construct(array $participants) {
        $this->setParticipants($participants);
    }

    public function setParticipants($participants) {
        $this->participants = $participants;
        if(($missing = $num_slots - count($this->participants)) > 0) {
            $this->participants = array_merge($this->participants, array_fill(0, count($missing), null));
        }
        if($missing < 0) {
            $participants_overflowing = array_splice($this->participants, $num_slots, -$missing);
            // TODO: Reassing overflowing participants according to the shuffle strategy
        }
    }

    public function count() {
        $count = 0;
        foreach($this->participants as $p) {
            if(!is_null($p)) {
                $count++;
            }
        }
        return $count;
    }

   /*
    fn set/get/Members
    fn hasOpenSlots // Check if completed
    fn addParticipant(participant) // Sets a Participant according to the SEEDING Strategy
    fn shuffle() // Moves the slots around according to the seeder's strategy
    fn render(Admin)


    fn setParticipantSlot(slotid, participant)
    fn switchParticipantSlot(participant1, participant2)
    fn removeParticipant(participant)
    fn replaceParticipant(slotid, participant)
    fn clearSlot(slotid)
   */

}
