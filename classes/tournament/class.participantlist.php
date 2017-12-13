<?php

namespace Forge\Modules\ForgeTournaments;

use \Forge\Core\App\App;
/**
 * The Pool contains a number of participants.
 * Each participant has a slot place
 * The slot place can be reassigned by shuffling
 */
class ParticipantList {
    // List of ids of participants of length $num_slots
    protected $participants;
    protected $num_slots;
    protected $seeder;

    public function __construct($num_slots, array $participants=[]) {
        $this->num_slots = $num_slots;
        $this->setParticipants($participants);
    }

    public function getParticipants() {
        return $this->participants;
    }

    public function setParticipants($participants) {
        $this->participants = $participants;
        if(($missing = $this->num_slots - count($this->participants)) > 0) {
            error_log(print_r($missing, 1));
            $this->participants = array_merge($this->participants, array_fill(0, $missing, null));
        }
        if($missing < 0) {
            $participants_overflowing = array_splice($this->participants, $this->num_slots, -$missing);
            // TODO: Reassing overflowing participants according to the shuffle strategy
        }
    }

    public function getSlot($slot_id) {
        if(!isset($this->participants[$slot_id]) || is_null($this->participants[$slot_id])) {
            return null;
        }
        return $this->participants[$slot_id];
    }

    public function addParticipant($participant) {
        $idx = $this->findNextSlot();
        if($idx === false) {
            return false;
        }
        $this->participants[$idx] = $participant;
    }

    private function findNextSlot() {
        foreach($this->participants as $idx => $p) {
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
        foreach($this->participants as $p) {
            if(!is_null($p)) {
                $count++;
            }
        }
        return $count;
    }

}
