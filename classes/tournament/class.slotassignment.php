<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
/**
 * The Pool contains a number of slots.
 * Each participant has a slot place
 * The slot place can be reassigned by shuffling
 */
class SlotAssignment {
    // List of ids of slots of length $num_slots
    protected $slots = [];
    protected $num_slots;
    protected $seeder;

    public function __construct($num_slots, array $slots=[]) {
        $this->num_slots = $num_slots;
        $this->setSlots($slots);
    }

    public function getSlots() {
        return $this->slots;
    }

    public function setSlots($slots) {
        if(($missing = $this->num_slots - count($slots)) > 0) {
            $slots = array_merge($slots, array_fill(0, $missing, $this->getMissingSlotValue()));
        }
        if($missing < 0) {
            $slots_overflowing = array_splice($slots, $this->num_slots, -$missing);
            // TODO: Reassing overflowing slots according to the shuffle strategy
        }

        $this->slots = $this->prepareSlots($slots);
    }

    protected function getMissingSlotValue() {
        return null;
    }

    protected function prepareSlots($slots) {
        $_slots = [];
        foreach($slots as $key => $slot) {
            $_slots[$key] = $this->prepareSlot($slot);
        }
        return $_slots;
    }

    protected function prepareSlot($slot) {
        return $slot;
    }

    public function getSlot($slot_id) {
        if(!isset($this->slots[$slot_id]) || is_null($this->slots[$slot_id])) {
            return null;
        }
        return $this->slots[$slot_id];
    }

    public function addEntry($entry) {
        $idx = $this->findNextSlot();
        if($idx === false) {
            return false;
        }
        $this->slots[$idx] = $this->prepareSlot($entry);
    }

    private function findNextSlot() {
        foreach($this->slots as $idx => $p) {
            if(is_null($p)) {
                return $idx;
            }
        }
        return false;
    }

    public function numSlots() {
        return $this->num_slots;
    }

    public function count() {
        $count = 0;
        foreach($this->slots as $p) {
            if(!is_null($p)) {
                $count++;
            }
        }
        return $count;
    }

}
