<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;

class ParticipantSlotAssignment extends SlotAssignment {

    protected function prepareSlot($slot) {
        if($slot instanceof \stdClass) {
            $slot = (array) $slot;
        }

        if(is_object($slot)) {
            return $slot;
        }

        if(is_null($slot)) {
            return null;
        }

        if(!isset($slot)) {
            return null;
        }
        
        $instance = PoolRegistry::instance()->getPool('collectionitem')->getInstance($slot);
        return $instance;
    }

    protected function prepareSlotData($slot) {
        if(is_null($slot)) {
            return null;
        }
        return $slot->getID();
    }

}
