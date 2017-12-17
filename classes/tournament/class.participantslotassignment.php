<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;

class ParticipantSlotAssignment extends SlotAssignment {

    protected function prepareSlot($slot) {
        if(is_object($slot)) {
            return $slot;
        }
        if(is_null($slot)) {
            return null;
        }
        return PoolRegistry::instance()->getPool('collectionitem')->getInstance($slot['value']);
    }

}
