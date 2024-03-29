<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;

/**
 * The Encounter determines which participant
 * will be victorius
 */
class Encounter extends HierarchicalEntity {
   // Best of 1 / Best of 3 / Best of 5
   // Performance
   protected $type;
   // A Round does have to have configurable fields based on the played game
   // Maybe Define Game-Types as in toornament so that these fields do not have
   // to be regenerated each time
   protected $rounds;

    public function addMatches($matches) {
        foreach($matches as $match) {
            $match->setParent($this);
        }
    }

   /*
    fn set/get/Members
    fn hasOpenSlots // Check if completed
    fn addParticipant(participant) // Sets a Participant according to the SEEDING Strategy
    fn render(Admin)


    fn setParticipantSlot(slotid, participant)
    fn switchParticipantSlot(participant1, participant2)
    fn removeParticipant(participant)
    fn clearSlot(slotid)
   */

}
