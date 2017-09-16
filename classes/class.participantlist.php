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
