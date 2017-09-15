<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
/**
 * The Pool contains a number of participants.
 * Each participant has a start place
 * The startplace can be reassigned by shuffling
 */
class Pool {
   protected $participants;
   protected $slots;
   protected $seeder;
   /*
    fn set/get/Members
    fn hasOpenSlots // Check if completed
    fn addParticipant(participant) // Sets a Participant according to the SEEDING Strategy
    fn shuffle(Random/BestScore/Magic) // Moves the slots around
    fn render(Admin)


    fn setParticipantSlot(slotid, participant)
    fn switchParticipantSlot(participant1, participant2)
    fn removeParticipant(participant)
    fn clearSlot(slotid)
   */

}
